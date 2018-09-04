<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductAttribute;
use App\Migration;
use App\Scrapping;

class MigrationDeals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:deals {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deals/Products Migration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function mapProductCategory()
    {
        $tmp = 
        [
            "Pistol: Derringer" => ProductCategory::$HANDSGUN_SPC_DERRINGER,
            "Pistol: Double Action Only" => 0,
            "Pistol: Lever Action" => ProductCategory::$HANDSGUN_SPC_LEVER_ACTION,
            "Pistol: Semi-Auto" => ProductCategory::$HANDSGUN_SEMI_AUTO,
            "Pistol: Single Shot" => ProductCategory::$HANDSGUN_SPC_SINGLE_SHOT,
            "Revolver: Double Action" => ProductCategory::$HANDSGUN_REVOLVER_DOUBLE_ACTION,
            "Revolver: Double Action Only" => ProductCategory::$HANDSGUN_REVOLVER_DOUBLE_ACTION_ONLY,
            "Revolver: Single Action" => ProductCategory::$HANDSGUN_REVOLVER_SINGLE_ACTION,
            "Rifle: Air Gun" => 0,
            "Rifle: Bolt Action" => ProductCategory::$RIFLES_BOLT_ACTION,
            "Rifle: Lever Action" => ProductCategory::$RIFLES_LEVER_ACTION,
            "Rifle: Muzzleloader" => ProductCategory::$RIFLES_MUZZLE_LOADER,
            "Rifle: Pump Action" => ProductCategory::$RIFLES_PUMP_ACTION,
            "Rifle: Semi-Auto" => ProductCategory::$RIFLES_SEMI_AUTO,
            "Rifle: Single Shot" => ProductCategory::$RIFLES_SINGLE_SHOT,
            "Rifle|Shotgun Combo: All" => 0,
            "Rifle|Shotgun: All" => 0,
            "Shotgun: Bolt Action" => ProductCategory::$SHOTGUNS_BOLT_ACTION,
            "Shotgun: Lever Action" => ProductCategory::$SHOTGUNS_LEVER_ACTION,
            "Shotgun: Over and Under" => ProductCategory::$SHOTGUNS_OVER_UNDER,
            "Shotgun: Pump Action" => ProductCategory::$SHOTGUNS_PUMP_ACTION,
            "Shotgun: Semi-Auto" => ProductCategory::$SHOTGUNS_SEMI_AUTO,
            "Shotgun: Side By Side" => ProductCategory::$SHOTGUNS_SIDE_BY_SIDE,
            "Shotgun: Single Shot" => ProductCategory::$SHOTGUNS_SINGLE_SHOT,
        ];

        $categoryARR = [];

        foreach($tmp as $k => $v)
        {
            $categoryARR[strtolower($k)] = $tmp[$k];
        }        

        $flag = true;
        $i = 0;
        $offset = 0;
        $limit = 100;

        while($flag)
        {
            $rows = ProductAttribute::select("id","product_id","keyname","keyvalue")
                    ->whereRaw("TRIM(LOWER(`keyname`)) = 'type'")
                    ->limit($limit)
                    ->offset($offset)                                    
                    ->get();

            $offset = $offset + $limit;        

            if($rows && count($rows) > 0)
            {
                foreach($rows as $row)
                {
                    $product_id = $row->product_id;
                    $keyvalue = trim(strtolower($row->keyvalue));

                    if(isset($categoryARR[$keyvalue]))
                    {
                        $categoryID = $categoryARR[$keyvalue];
                        if($categoryID > 0)
                        {
                            \DB::table("products")
                            ->where("id",$product_id)
                            ->update
                            (
                                [
                                    "product_category_id" => $categoryID
                                ]
                            );
                        }
                    }

                    $i++;
                    echo "\n$i";                    
                }
            }
            else
            {
                $flag = false;
            }        
        }
    }

    /**
     * Scrap Detail Page Of Gallery Of Guns
     *
     */
    public function scrapGunDetails()
    {
       $i = 0;
       $offset = 0;
       $limit = 100;       
       while(true)
       {
            $rows = \DB::table("galleryofguns")                    
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

            $offset = $offset + $limit;
            if($rows && count($rows))
            {
                foreach($rows as $row)
                {
                    $link = $row->link;
                    $mainID = $row->id;

                    echo "\nPage URL: ".$link;                    

                    $data = Scrapping::scrapGuns($link, "detail");

                    if(count(array_keys($data)) > 0 )
                    {
                        $dataToUpdate = 
                        [
                            "image" => $data['image'],
                            "thumb_image" => $data['thumb_image'],
                            "item_unique_id" => $data['item'],
                            "msrp" => $data['msrp'],
                            "attr" => json_encode($data['attr'])
                        ];

                        \DB::table("galleryofguns")
                        ->where("id",$mainID)
                        ->update($dataToUpdate);
                    }

                    $i++;
                    echo "\n$i processed!";
                }   
            }
            else
            {
                break;
            }
       }
    }

    /**
     * Scrap Master Links Of Gallery Of Guns
     *
     */
    public function scrapGuns($link, $type)
    {
        $url = $link;
        $page = 0;
        $counter = 0;
        $flag = true;
        $newAdded = 0;
        while($flag)
        {
            $pageUrl = $url;

            if($page > 0)
            $pageUrl = $url."&index=$page";

            $rows = Scrapping::scrapGuns($pageUrl, "master");

            if(is_array($rows) && count($rows) > 0)
            {
                foreach($rows as $row)
                {
                    $title = trim($row['title']);
                    $link = trim($row['link']);
                    $itemID = $row['itemID'];
                    $linkMD5 = md5($itemID);

                    if(!empty($itemID))
                    {
                        $existObj = DB::table("galleryofguns")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("galleryofguns")
                            ->insert
                            (
                                [
                                    "item_id" => $itemID,
                                    "category" => $type,
                                    "title" => $title,
                                    "link" => $link,
                                    "link_md5" => $linkMD5,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                            $newAdded++;
                        }
                    }


                    $counter++;
                    echo "\n$counter processed!";
                }
            }
            else
            {
                $flag = false;
            }

            $page++;
        }                        

        return ['total' => $counter,"new" => $newAdded];
    }

    public function handle()
    {        
        $type = $this->argument("type");

        $scriptStartTime = date("Y-m-d H:i:s");
        $content = [];


        // Scrap products from          
        if($type == "grap-product")
        {
            $links[] = 
            [
                'type' => "Handguns All Types",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=1&type=All&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Revolver All Types",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Revolver&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Rifle All Types",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Rifle&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Bolt Action",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?mfg=All&mdl=All&cat=All&type=Shotgun%3a+Bolt+Action&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Lever Action",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Lever+Action&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Over Under",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Over+and+Under&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Pump Action",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Pump+Action&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Side by Side",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Side+by+Side&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Single Shot",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Single+Shot&cal=All&rebate=No&zipcode=49464"
            ];        

            $links[] = 
            [
                'type' => "Shotgun Semi-Auto",
                "link" => "https://www.galleryofguns.com/genie/PowerSearchTabView/SearchResultsFirearms.aspx?&mfg=All&mdl=All&cat=All&type=Shotgun%3a+Semi-Auto&cal=All&rebate=No&zipcode=49464"
            ];        

            $cron_id = 10;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);

            $overall_total = 0;
            $overall_new = 0;
            foreach($links as $row)
            {
                $link = $row['link'];
                $type = $row['type'];                    

                $res = $this->scrapGuns($link,$type);

                $overall_total = $overall_total + $res['total'];
                $overall_new = $overall_new + $res['new'];
            }

            $this->scrapGunDetails();

            $content = 
            [
                "total" => $overall_total, 
                "new" => $overall_new
            ];
        }
        else if($type == "migrate-product")
        {
            $cron_id = 11;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);
            $content = Migration::migrateMasterProducts();
        }
        else if($type == "migrate-deals")
        {
            Migration::mapDeals();
            exit;
        }
        else if($type == "map-category")
        {
            $this->mapProductCategory();
            exit;
        }
        else if($type == "scrap-ammo")
        {
            $this->scrapAmmo();
            exit;
        }
        else if($type == "scrap-ammo-map-deal")
        {
            $this->scrapAmmoMapDeal();
            exit;
        }
        else
        {
            exit("Invalid cron type!");
        }

        $scriptEndTime = date("Y-m-d H:i:s");                
        storeCronLogs($scriptStartTime, $scriptEndTime, NULL, $content, 'Web Server', $cron_id, $mainLogID);        
    }   

    function scrapAmmoMapDeal()
    {
        $i = 0;
        $offset = 0;
        $limit = 100;
        while(true)
        {
            $rows = DB::table("deal_specifications as ds")
                    ->select("deals.id","ds.value as upc")
                    ->join("deals","deals.id","=","ds.deal_id")
                    ->whereRaw("TRIM(LOWER(ds.`key`)) = 'upc' AND deals.product_id IS NULL")
                    ->limit($limit)
                    ->offset($offset)
                    ->get();                    

            $offset = $offset + $limit;      

            if($rows && count($rows))
            {
                foreach($rows as $row)
                {
                    
                    $dealID = $row->id;
                    $upc = trim($row->upc);
                    echo "\nUPC: ".$upc;
                    $i++;
                    echo "\n$i processed!";

                    $product = \App\Models\AmmoProduct::where("upc_number",$upc)->first();

                    if($product)
                    {
                        \DB::table("deals")
                        ->where("id",$dealID)
                        ->update
                        (
                            [
                                "ammo_product_id" => $product->id 
                            ]
                        );                        
                    }
                    
                    unset($product);
                }
            }  
            else
            {
                break;
            }
        }        
    }

    function scrapAmmo()
    {
        $type = "Handgun Ammo";
        $typeID = 36;
        // $this->scrapMasterLinks($type, $typeID);

        $type = "Rimfire Ammo";
        $typeID = 37;
        // $this->scrapMasterLinks($type, $typeID);

        $type = "Rifle Ammo";
        $typeID = 38;
        // $this->scrapMasterLinks($type, $typeID);        

        $type = "Shotgun Ammo";
        $typeID = 39;
        // $this->scrapMasterLinks($type, $typeID);        

        $this->scrapAmmoDetails();
    }

    function scrapAmmoDetails()
    {
       $i = 0;
       $offset = 0;
       $limit = 100;
       $counter = 0;
       $date = date("Y-m-d");

       while(true)
       {
            $rows = \DB::table("scrap_ammo_products")
            ->limit($limit)
            ->offset($offset)                
            ->get();

            $offset = $offset + $limit;

            if($rows && count($rows))
            {   
                foreach($rows as $row)
                {                  
                    $mainID = $row->id;                    
                    $url = $row->link;  
                    echo "\nUrl: ".$url;            

                    $res = \App\Scrapping::scrapAmmo($url,"detail");                                                        

                    if(isset($res['title']))
                    {
                        $title = trim($res['title']);
                        $price = trim($res['price']);
                        $sale_price = trim($res['sale_price']);                        
                        $desc = trim($res['desc']);
                        $images = $res['images'];
                        $attr = $res['attr'];

                        $image = isset($images[0]) ? $images[0]:"";
                        $item_unique_id = "";
                        $upc_number = "";

                        foreach($attr as $k => $r)
                        {
                            if($r['key'] == "UPC Barcode")
                            {
                                $upc_number = $r['value'];
                                unset($attr[$k]);
                            }
                            else if($r['key'] == "Manufacturer SKU")
                            {
                                $item_unique_id = $r['value'];
                                unset($attr[$k]);
                            }
                        }

                        $product_id = null;

                        if(!empty($upc_number))
                        $product_id = "GR-".$upc_number;


                        $dataToUpdate = 
                        [
                            "product_id" => $product_id,
                            "title" => $title,
                            "description" => $desc,
                            "image" => $image,
                            "item_unique_id" => $item_unique_id,
                            "upc_number" => $upc_number,
                            "attr" => json_encode($attr),
                            "images" => json_encode($images),
                        ];

                        \DB::table("scrap_ammo_products")
                        ->where("id",$mainID)
                        ->update($dataToUpdate);

                        $obj = \DB::table("scrap_ammo_prices")
                        ->where("parent_id",$mainID)
                        ->where("date",$date)
                        ->first();

                        if($obj)
                        {
                            \DB::table("scrap_ammo_prices")
                            ->where("id",$obj->id)
                            ->update([
                                "regular_price" => $price,
                                "sale_price" => $sale_price,
                            ]);
                        }
                        else
                        {
                            \DB::table("scrap_ammo_prices")
                            ->insert([
                                "regular_price" => $price,
                                "sale_price" => $sale_price,
                                "parent_id" => $mainID,
                                "date" => $date
                            ]);
                        }

                    }

                    $counter++;
                    echo "\n $counter";
                }
            }   
            else
            {
                break;
            } 
       }            
    }

    function scrapMasterLinks($type, $typeID)
    {
        if($type == "Handgun Ammo")
        {            
            $link = "https://www.luckygunner.com/handgun";
            echo "\n Page: ".$link;
            $links = Scrapping::scrapAmmo($link, "handgun_master");
            $counter = 0;

            foreach($links as $link)
            {
                $link = $link['link']."?limit=all";
                echo "\n Sub Page: ".$link;
                $rows = Scrapping::scrapAmmo($link, "handgun_master_1");                
                foreach($rows as $row)
                {
                    $pageLink = $row['link'];

                    if(!empty($pageLink))
                    {
                        $linkMD5 = md5($pageLink);
                        $existObj = DB::table("scrap_ammo_products")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("scrap_ammo_products")
                            ->insert
                            (
                                [
                                    "product_category_id" => $typeID,
                                    "link" => $pageLink,
                                    "link_md5" => $linkMD5,
                                    "from_url" => $link,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                        }

                        $counter++;                        
                        echo "\n".$counter;
                    }
                }
            }
        }        
        else if($type == "Rifle Ammo")
        {            
            $link = "https://www.luckygunner.com/rifle";
            echo "\n Page: ".$link;
            $links = Scrapping::scrapAmmo($link, "handgun_master");
            $counter = 0;

            foreach($links as $link)
            {
                $link = $link['link']."?limit=all";
                echo "\n Sub Page: ".$link;
                $rows = Scrapping::scrapAmmo($link, "handgun_master_1");                
                foreach($rows as $row)
                {
                    $pageLink = $row['link'];

                    if(!empty($pageLink))
                    {
                        $linkMD5 = md5($pageLink);
                        $existObj = DB::table("scrap_ammo_products")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("scrap_ammo_products")
                            ->insert
                            (
                                [
                                    "product_category_id" => $typeID,
                                    "link" => $pageLink,
                                    "link_md5" => $linkMD5,
                                    "from_url" => $link,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                        }

                        $counter++;                        
                        echo "\n".$counter;
                    }
                }
            }
        }        
        else if($type == "Rimfire Ammo")
        {            
            $link = "https://www.luckygunner.com/rimfire?limit=all";
            echo "\n Page: ".$link;

            $links = Scrapping::scrapAmmo($link, "rimfire_master");
            $counter = 0;
            foreach($links as $row)
            {
                $pageLink = $row['link'];

                if(!empty($pageLink))
                {
                    $linkMD5 = md5($pageLink);
                    $existObj = DB::table("scrap_ammo_products")->where("link_md5",$linkMD5)->first();
                    if(!$existObj)
                    {
                        \DB::table("scrap_ammo_products")
                        ->insert
                        (
                            [
                                "product_category_id" => $typeID,
                                "link" => $pageLink,
                                "link_md5" => $linkMD5,
                                "from_url" => $link,
                                "created_at" => date("Y-m-d H:i:s")
                            ]
                        );
                    }

                    $counter++;                        
                    echo "\n".$counter;
                }
            }

            $links = Scrapping::scrapAmmo($link, "rimfire_master_1");
            foreach($links as $link)
            {
                $link = $link['link']."?limit=all";
                echo "\n Sub Page: ".$link;
                $rows = Scrapping::scrapAmmo($link, "rimfire_master_2");                
                foreach($rows as $row)
                {
                    $pageLink = $row['link'];

                    if(!empty($pageLink))
                    {
                        $linkMD5 = md5($pageLink);
                        $existObj = DB::table("scrap_ammo_products")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("scrap_ammo_products")
                            ->insert
                            (
                                [
                                    "product_category_id" => $typeID,
                                    "link" => $pageLink,
                                    "link_md5" => $linkMD5,
                                    "from_url" => $link,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                        }

                        $counter++;                        
                        echo "\n".$counter;
                    }
                }

            }

        }
        else if($type == "Shotgun Ammo")
        {            
            $link = "https://www.luckygunner.com/shotgun";
            echo "\n Page: ".$link;

            $counter = 0;

            $links = Scrapping::scrapAmmo($link, "shotgun_master");
            foreach($links as $link)
            {
                $link = $link['link']."?limit=all";
                echo "\n Sub Page: ".$link;
                $rows = Scrapping::scrapAmmo($link, "rimfire_master_2");                
                foreach($rows as $row)
                {
                    $pageLink = $row['link'];

                    if(!empty($pageLink))
                    {
                        $linkMD5 = md5($pageLink);
                        $existObj = DB::table("scrap_ammo_products")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("scrap_ammo_products")
                            ->insert
                            (
                                [
                                    "product_category_id" => $typeID,
                                    "link" => $pageLink,
                                    "link_md5" => $linkMD5,
                                    "from_url" => $link,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                        }

                        $counter++;                        
                        echo "\n".$counter;
                    }
                }
            }
        }
    }
}