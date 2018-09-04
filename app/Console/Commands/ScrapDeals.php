<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapSourceUrl;
use App\Scrapping;

class ScrapDeals extends Command
{
    protected $signature = 'scrap:deals {type}';

    protected $description = 'Scrape Deals';

    public function __construct()
    {
        parent::__construct();        
    }

    public function startScrapping($source_id,$params)
    {
        $scrap_urls = \Config::get("app.scrap_urls");
        $scrapType = "";
        if(isset($scrap_urls[$source_id]))
        {
            $scrapType = $scrap_urls[$source_id];
        }

        $res = [];

        switch($scrapType)
        {
            case 'SCRAP_GRABGUN_DEALS':
                      // $this->scrap_grabgun_deals($params);
                      break;
            case 'SCRAP_GRABGUN_PRODUCTS':
                      $this->scrap_grabgun_products($params);
                      break;
            default:
                      echo "\n No source founded!";  
                      break;
        }

        return $res;
    }

    public function createDeal($res)
    {
        $linkMD5 = $res['linkMD5'];
        $link = $res['link'];
        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
        $title = $res['name'];

        $dataToUpdate = 
        [
            "source_id" => $res['source_id'],
            "title" => $title,
            "link" => $link,            
            "unique_md5" => $linkMD5,
            "out_of_stock" => $res["out_of_stock"],
            "description" => $res["description"],
            "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
            "sale_price" => $res["special_price"],
            "base_price" => $res["old_price"],
            "ext_date" => $res["ext_date"],
            "save_price" => $res["saving_price"],                                            
        ];        

        $dataToUpdate["last_visit_date"] = date("Y-m-d H:i:s");
        $dataToUpdate["updated_at"] = date("Y-m-d H:i:s");

        if($res['category_id'] > 0)
        $dataToUpdate["category_id"] = $res['category_id'];

        if(isset($res['from_url']))
        {
            $dataToUpdate["from_url"] = $res['from_url'];
        }
    

        $specifications = $res['specification'];    
        $upc_number = "";

        if(count($specifications) > 0)
        {

            foreach($specifications as $row)
            {
                if(trim(strtolower($row['key'])) == "upc")
                {
                    $upc_number = $row['value'];
                }
            }                    
        }               

        $unique_id = null;

        if(!empty($upc_number))
        $unique_id = "GR-".$upc_number;

        $dataToUpdate["unique_id"] = $unique_id;
        $dataToUpdate["upc_number"] = $upc_number;

        if(!$isExist)
        {
            $dataToUpdate["created_at"] = date("Y-m-d H:i:s");
            $deal_id = \DB::table("deals")->insertGetId($dataToUpdate);

            // Deal Prices
            \DB::table("deal_prices")
            ->insert
            (
                [
                    "deal_id" => $deal_id,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "date" => date("Y-m-d")
                ]
            );
        }
        else
        {
            $deal_id = $isExist->id;
            \DB::table("deals")
            ->where("id",$deal_id)
            ->update($dataToUpdate);            

            // Deal Prices
            if(($isExist->sale_price != $res["special_price"]) || ($isExist->base_price != $res["old_price"]))
            {
                \DB::table("deal_prices")
                ->insert
                (
                    [
                        "deal_id" => $deal_id,
                        "sale_price" => $res["special_price"],
                        "base_price" => $res["old_price"],
                        "date" => date("Y-m-d")
                    ]
                );
            }
        }

        // Add Photos
        \DB::table("deal_photos")->where("deal_id",$deal_id)->delete();
        $images = $res['images'];    
        if(count($images) > 0)
        {
            $dataToInsert = [];
            foreach($images as $row)
            {
                $image = $row['image'];                        
                $dataToInsert[] = [
                    "deal_id" => $deal_id,
                    "image_url" => $image,
                    "created_at" => date("Y-m-d H:i:s"),
                ];
            }                    

            \DB::table("deal_photos")->insert($dataToInsert);
        }

        // Add Specifications                
        \DB::table("deal_specifications")->where("deal_id",$deal_id)->delete();
        
        if(count($specifications) > 0)
        {
            $dataToInsert = [];
            foreach($specifications as $row)
            {
                $dataToInsert[] = 
                [
                    "deal_id" => $deal_id,
                    "key" => $row['key'],
                    "value" => $row['value'],
                    "created_at" => date("Y-m-d H:i:s"),
                ];
            }                    

            \DB::table("deal_specifications")->insert($dataToInsert);
        }               

    }

    public function createProduct($res)
    {
        $linkMD5 = $res['linkMD5'];
        $link = $res['link'];        
        $title = $res['title'];
        $isExist = \DB::table("products")->where("link_md5",$linkMD5)->first();
        $specifications = $res['attr'];

        if(count($specifications) > 0)
        {
            foreach($specifications as $row)
            {
                if(trim(strtolower($row['key'])) == "upc")
                {
                    $upc_number = $row['value'];
                }
            }                    
        }               

        $unique_id = null;

        if(!empty($upc_number))
        $unique_id = "GR-".$upc_number;

        $image = "";

        $images = $res['images'];    
        if(count($images) > 0)
        {
            foreach($images as $row)
            {
                $image = $row['image'];                        
                break;
            }                    
        }        

        $dataToInsert = 
        [
            "source_id" => $res['source_id'],
            "product_id" => $unique_id,
            "title" => $title,
            "link" => $link,
            "link_md5" => $linkMD5,
            "image" => $image,
            "upc_number" => $upc_number,
            "sale_price" => $res["special_price"],
            "base_price" => $res["old_price"]
        ];

        if($res['category_id'] > 0)
        $dataToInsert["product_category_id"] = $res['category_id'];

        if(isset($res['from_url']))
        {
            $dataToInsert["from_url"] = $res['from_url'];
        }

        $dataToInsert["last_visit_date"] = date("Y-m-d H:i:s");
        $dataToInsert['created_at'] = date("Y-m-d H:i:s");

        if($isExist)
        {
            $productId = $isExist->id;

            \DB::table("product_attributes")
            ->where("id",$productId)
            ->delete();

            $dataToInsert['updated_at'] = $dataToInsert['created_at'];
            unset($dataToInsert['created_at']);

            \DB::table("products")
            ->where("id",$productId)
            ->update($dataToInsert);

            // Deal Prices
            if(($isExist->sale_price != $res["special_price"] && !empty($res["special_price"])) || ($isExist->base_price != $res["old_price"] && !empty($res["old_price"])))
            {
                \DB::table("product_prices")
                ->insert
                (
                    [
                        "product_id" => $productId,
                        "sale_price" => $res["special_price"],
                        "base_price" => $res["old_price"],
                        "date" => date("Y-m-d")
                    ]
                );
            }

        }
        else
        {
            $productId = \DB::table("products")
            ->insertGetId($dataToInsert);            

            // Deal Prices
            \DB::table("product_prices")
            ->insert
            (
                [
                    "product_id" => $productId,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "date" => date("Y-m-d")
                ]
            );            
        }        

        if(count($specifications) > 0)
        {
            foreach($specifications as $r)
            {
                \DB::table("product_attributes")
                ->insert([
                    "product_id" => $productId,
                    "keyname" => $r["key"],
                    "keyvalue" => $r["value"],
                ]);
            }
        }        
    }

    public function scrap_grabgun_products($params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];
        $masterLinks = Scrapping::scrapGrabGunsProductLinks($scrap_url);        
        // $masterLinks[] = [
        //     "link" => "https://grabagun.com/s-w-642-1-875-38spl-sts-alum-cent.html",            
        // ];
        // $masterLinks[] = [
        //     "link" => "https://grabagun.com/s-w-m-p9shield-10035-9m-3-1-7-8r-nms.html?source=igodigital",
        // ];
        echo "Total Links: ".count($masterLinks);
        foreach($masterLinks as $link)
        {
            $url = $link['link'];
            $link = trim($url);
            $res = \App\Scrapping::deal_scraps("grabagun_detail",$link);            
            if(array_keys($res) > 0)
            {                
                $linkMD5 = md5($link);                                
                $res['link'] = $link;
                $res['linkMD5'] = $linkMD5;                
                $res['category_id'] = $category_id;
                $res['source_id'] = $source_id;
                $res['from_url'] = $scrap_url;

                // create/update record
                if($source_type == 1)
                {
                    $response['title'] = $res['name'];
                    $response['link'] = $res['link'];
                    $response['linkMD5'] = $res['linkMD5'];
                    $response['category_id'] = $res['category_id'];
                    $response['source_id'] = $res['source_id'];
                    $response['images'] = $res['images'];
                    $response['attr'] = $res['specification'];
                    $response['special_price'] = $res['special_price'];
                    $response['old_price'] = $res['old_price'];                    
                    $response['from_url'] = $scrap_url;
                    $this->createProduct($response);                
                }
                else
                {
                    $this->createDeal($res);                
                }
            }            
        }        
    }

    public function scrap_grabgun_deals($params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];

        $masterLinks = Scrapping::scrapGrabGunsListingLinks($scrap_url);        

        echo "\n Total Links: ".count($masterLinks);  

        foreach($masterLinks as $link)
        {
            $url = $link['link'];
            $link = trim($url);
            $res = \App\Scrapping::deal_scraps("grabagun_detail",$link);
            if(array_keys($res) > 0)
            {                
                $linkMD5 = md5($link);                                
                $res['link'] = $link;
                $res['linkMD5'] = $linkMD5;                
                $res['category_id'] = $category_id;
                $res['source_id'] = $source_id;
                $res['from_url'] = $scrap_url;
                // create/update record
                if($source_type == 1)
                {
                    $response['title'] = $res['name'];
                    $response['link'] = $res['link'];
                    $response['linkMD5'] = $res['linkMD5'];
                    $response['category_id'] = $res['category_id'];
                    $response['source_id'] = $res['source_id'];
                    $response['images'] = $res['images'];
                    $response['attr'] = $res['specification'];                    
                    $response['special_price'] = $res['special_price'];
                    $response['old_price'] = $res['old_price'];                                        
                    $response['from_url'] = $scrap_url;
                    $this->createProduct($response);                
                }
                else
                {
                    $this->createDeal($res);                
                }
            }
        }
    }

    public function handle()
    {        
        $type = $this->argument("type");
        $scriptStartTime = date("Y-m-d H:i:s");
        

        $content = [];

        if($type == "all")
        {
            // $cron_id = 1;            
            // $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);
            $rows = ScrapSourceUrl::where("status",1)->get();

            foreach($rows as $row)
            {                
                $params = 
                [
                    "source_id" => $row->source_id,
                    "category_id" => $row->category_id,
                    "scrap_url" => $row->scrap_url,
                    "source_type" => $row->source_type,
                ];

                $this->startScrapping($row->source_id,$params);
            }            

            exit;
        }
        else
        {
            exit("Invalid cron type!");
        }

        $scriptEndTime = date("Y-m-d H:i:s");                
        storeCronLogs($scriptStartTime, $scriptEndTime, NULL, $content, 'Web Server', $cron_id, $mainLogID);
    }        
}