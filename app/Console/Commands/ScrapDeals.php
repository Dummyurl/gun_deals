<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScrapDeals extends Command
{
    protected $signature = 'scrap:deals {type}';

    protected $description = 'Scrape Deals';

    public function __construct()
    {
        parent::__construct();
    }

    public function mapCategory($categories, $mainID)
    {
        $lastID = 0;
        
        foreach($categories as $key => $val)
        {
            $categoryID = \App\Models\Category::getCategory($val,$lastID);
            $lastID = $categoryID;
        }
        
        \DB::table("deals")
        ->where("id",$mainID)        
        ->update(["category_id" => $lastID]);
    }

    public function scrapEArmsMasterLinks()
    {
        $flag = true;
        $i = 1;
        $cnt = 0;
        $newAdded = 0;
        while($flag)
        {

            $url = "https://e-arms.com/hot-deals/?page=$i";
            $rows = \App\Scrapping::deal_scraps("e-arms",$url);

            echo "\n$url";

            if(is_array($rows) && count($rows))
            {
                    foreach($rows as $link)
                    {
                        $linkMD5 = md5($link);
                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 2,
                                "title" => "",
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";

                    }

            }
            else
            {
                $flag = false;
            }

            $i++;
        }             

        return ['total' => $cnt,"new" => $newAdded];   
    }
    public function scrapEArmsDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",2)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;

            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;
            $res = \App\Scrapping::deal_scraps("earms_detail",$url);

            $categories = $res['categories'];            

            if(count($categories))
            {
                unset($categories[count($categories)-1]);
            }

            $this->mapCategory($categories, $mainID);

            if(array_keys($res) > 0 && false)
            {
                $source_id = 2;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");

                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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

            
            echo "\ncounter: ".$counter;            
        }
    }

    public function scrapPrimaryArmsMasterLinks()
    {
        $flag = true;
        $i = 1;
        $cnt = 0;
        $newAdded = 0;
        while($flag)
        {
            $url = "https://www.primaryarms.com/AdName+Clearance,Sale?show=96&page=$i";
            $rows = \App\Scrapping::deal_scraps("primaryarms",$url);

            echo "\n$url";
            // print_r($rows);
            if(is_array($rows) && count($rows))
            {
                    foreach($rows as $link)
                    {
                        $linkMD5 = md5($link);
                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 3,
                                "title" => "",
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }

            }
            else
            {
                $flag = false;
            }

            $i++;
        }                

        return ['total' => $cnt,"new" => $newAdded];
    }
    public function scrapPrimaryArmsDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",3)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;

            $res = \App\Scrapping::deal_scraps("primaryarms_detail",$url);

            if(array_keys($res) > 0)
            {
                $source_id = 3;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");

                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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

            $counter++;
            echo "\ncounter: ".$counter;            
        }
    }

    public function scrapBrownellsMasterLinks()
    {
        $flag = true;
        $i = 1;
        $cnt = 0;
        $offset = 1;
        $newAdded = 0;
        while($flag)
        {
            $url = "https://www.brownells.com/search/index.htm?avs%7cSpecial-Filters_1=Salezz1zzClearance%7cRebate&psize=96";

            if($i > 0)
            {
                $offset = $offset + 96;
                $url .= "&f_a=".$offset;
            }
            
            $rows = \App\Scrapping::deal_scraps("brownells",$url);

            echo "\n$url";

            if(is_array($rows) && count($rows) > 0)
            {
                    foreach($rows as $link)
                    {
                        $linkMD5 = md5($link);
                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 4,
                                "title" => "",
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }
            }
            else
            {
                $flag = false;
            }

            $i++;
        }              

        return ['total' => $cnt,"new" => $newAdded];  
    }
    public function scrapBrownellsDetailLinks()
    {
           $i = 0;
           $offset = 0;
           $limit = 100;
           $counter = 0;
           while(true)
           {
                $rows = \DB::table("deals")
                ->where("source_id",4)
                ->limit($limit)
                ->offset($offset)                
                ->get();

                $offset = $offset + $limit;

                if($rows && count($rows))
                {
                    foreach ($rows as $row) 
                    {
                        $counter++;            
                        
                        $url = $row->link;
                        $urlMD5 = md5($row->link);
                        $mainID = $row->id;

                        echo "\nUrl: ".$url;            
                        $res = \App\Scrapping::deal_scraps("brownells_detail",$url);                                    
                        $categories = $res['categories'];            
                        if(count($categories))
                        {
                            unset($categories[count($categories)-1]);
                        }
                        
                        $this->mapCategory($categories, $mainID);
                        
                        if(array_keys($res) > 0 && false)
                        {
                            $source_id = 4;

                            $dataToUpdate = 
                            [
                                "source_id" => $source_id,
                                "title" => $res["name"],
                                "out_of_stock" => $res["out_of_stock"],
                                "description" => $res["description"],
                                "link" => $url,
                                "from_url" => $url,
                                "unique_md5" => $urlMD5,
                                "sale_price" => $res["special_price"],
                                "base_price" => $res["old_price"],
                                "ext_date" => $res["ext_date"],
                                "save_price" => $res["saving_price"],                    
                            ];                

                            $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                            $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                            $deal_id = $mainID;

                            \DB::table("deals")
                            ->where("id",$deal_id)
                            ->update($dataToUpdate);

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
                            $specifications = $res['specification'];    
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
                        
                        echo "\ncounter: ".$counter;            
                    }
                }
                else
                {
                    break;
                }
           }
    }

    public function scrapSgammoMasterLinks()
    {        
        $cnt = 0;
        $url = "https://www.sgammo.com/new-this-month";
        $totalPages = \App\Scrapping::deal_scraps("sgammo_count",$url);
        echo "\nTotal Pages: ".$totalPages;        
        $newAdded = 0;
        for($j=0;$j<=$totalPages;$j++)
        {
            if($j > 0)
            {
                $url = "https://www.sgammo.com/new-this-month?page=$j";                
            }
            
            $rows = \App\Scrapping::deal_scraps("sgammo",$url);

            echo "\n$url";

            if(is_array($rows) && count($rows) > 0)
            {
                    foreach($rows as $link)
                    {
                        $linkMD5 = md5($link);
                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 5,
                                "title" => "",
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }
            }
            
        }

        return ['total' => $cnt,"new" => $newAdded];
    }    
    public function scrapSgammoDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",5)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;            
            
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;            
            $res = \App\Scrapping::deal_scraps("sgammo_detail",$url);                                    


            
            if(array_keys($res) > 0)
            {

                $categories = $res['categories'];            

                if(count($categories))
                {
                    $this->mapCategory($categories, $mainID);
                }                                


                $source_id = 5;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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
            
            echo "\ncounter: ".$counter;            
        }        
    }

    public function scrapRighttoBearMasterLinks(){

        $cnt = 0;
        $url = "https://www.righttobear.com/dealoftheday.asp";        
        $rows = \App\Scrapping::deal_scraps("righttobear_deals",$url);
        echo "\n$url";
        $newAdded = 0;
        if(is_array($rows) && count($rows) > 0)
        {
                foreach($rows as $link)
                {
                    $linkMD5 = md5($link);
                    $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                    if(!$isExist)
                    {
                        \DB::table("deals")
                        ->insert([
                            "source_id" => 6,
                            "title" => "",
                            "link" => $link,
                            "unique_md5" => $linkMD5,
                            "created_at" => date("Y-m-d H:i:s")
                        ]);
                        $newAdded++;
                    }

                    $cnt++;
                    echo "\n".$cnt." records processed.";
                }
        }

        $url = "https://www.righttobear.com/discount-AR-parts-s/1913.htm?searching=Y&sort=11&cat=1913&show=600&page=1";
        $rows = \App\Scrapping::deal_scraps("righttobear_sales",$url);
        echo "\n$url";

        if(is_array($rows) && count($rows) > 0)
        {
                foreach($rows as $link)
                {
                    $linkMD5 = md5($link);
                    $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                    if(!$isExist)
                    {
                        \DB::table("deals")
                        ->insert([
                            "source_id" => 6,
                            "title" => "",
                            "link" => $link,
                            "unique_md5" => $linkMD5,
                            "created_at" => date("Y-m-d H:i:s")
                        ]);

                        $newAdded++;
                    }

                    $cnt++;
                    echo "\n".$cnt." records processed.";
                }
        }

        return ['total' => $cnt,"new" => $newAdded];
    }
    public function scrapRighttoBearDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",6)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;            
            
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;            
            $res = \App\Scrapping::deal_scraps("righttobear_detail",$url);

            // print_r($res);
            // exit;
            
            if(array_keys($res) > 0)
            {

                // $categories = $res['categories'];            

                // if(count($categories))
                // {
                //     $this->mapCategory($categories, $mainID);
                // }                                

                $source_id = 6;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

                // Add Photos
                \DB::table("deal_photos")->where("deal_id",$deal_id)->delete();
                $images = $res['images'];    
                if(count($images) > 0)
                {
                    $dataToInsert = [];
                    $tmpI = [];
                    foreach($images as $row)
                    {
                        $image = $row['image'];                        
                        if(!isset($tmpI[$image]))
                        {
                            $tmpI[$image] = 0;                        
                            $dataToInsert[] = [
                                "deal_id" => $deal_id,
                                "image_url" => $image,
                                "created_at" => date("Y-m-d H:i:s"),
                            ];
                        }
                    }                    

                    \DB::table("deal_photos")->insert($dataToInsert);
                }

                // Add Specifications                
                \DB::table("deal_specifications")->where("deal_id",$deal_id)->delete();
                $specifications = $res['specification'];    
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
            
            echo "\ncounter: ".$counter;            
        }
    }

    public function scrapPreppergunshopMasterLinks()
    {
        $cnt = 0;
        $url = "https://www.preppergunshop.com/sale?limit=150";
        $totalPages = \App\Scrapping::deal_scraps("preppergunshop_count",$url);
        echo "\nTotal Pages: ".$totalPages;        

        $newAdded = 0;  

        for($j=1;$j<=$totalPages;$j++)
        {
            $url = "https://www.preppergunshop.com/sale?limit=150&p=$j";                            
            
            $rows = \App\Scrapping::deal_scraps("preppergunshop",$url);

            echo "\n$url";

            if(is_array($rows) && count($rows) > 0)
            {
                    foreach($rows as $link)
                    {
                        $linkMD5 = md5($link);
                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();

                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 9,
                                "title" => "",
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }
            }
            
        }

        return ['total' => $cnt,"new" => $newAdded];
    }
    public function scrapPreppergunshopDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",9)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;            
            
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;            
            $res = \App\Scrapping::deal_scraps("preppergunshop_detail",$url);

            // print_r($res);
            // exit;
            
            if(array_keys($res) > 0)
            {

                // $categories = $res['categories'];            

                // if(count($categories))
                // {
                //     $this->mapCategory($categories, $mainID);
                // }                                

                $source_id = 9;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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
            
            echo "\ncounter: ".$counter;            
        }
    }

    public function scrapPsaMasterLinks()
    {
        $url = "https://palmettostatearmory.com/daily-deals-new.html";

        $total_records = \App\Scrapping::deal_scraps("palmettostatearmory_count",$url);
        
        $cnt = 0;
        $newAdded = 0;
        if($total_records > 0)
        {
            $recordsPerPage = 30;
            $pages = ceil($total_records / $recordsPerPage);
            for($i = 1;$i<=$pages;$i++)
            {
                $url = "https://palmettostatearmory.com/daily-deals-new.html?p=$i";
                $rows = \App\Scrapping::deal_scraps("palmettostatearmory",$url);
                if(count($rows) > 0)
                {
                    foreach($rows as $row)
                    {
                        $name = trim($row['name']);
                        $link = trim($row['link']);
                        $linkMD5 = md5($link);

                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {
                            \DB::table("deals")
                            ->insert([
                                "source_id" => 1,
                                "title" => $name,
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);
                            $newAdded++;
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }
                }
            }
        }

        return ['total' => $cnt,"new" => $newAdded];
    }    
    public function scrapPsaDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",1)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;
            $res = \App\Scrapping::deal_scraps("palmettostatearmory_detail",$url);            
            if(array_keys($res) > 0)
            {
                $source_id = 1;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "image" => $res["image"],                    
                    "description" => $res["description"],
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "ratings" => $res["stars"],
                    "reviews_count" => $res["review_count"],
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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

            $counter++;
            echo "\ncounter: ".$counter;            
        }
    }    

    public function scrapGrabagunMasterLinks()
    {
        $url = "https://grabagun.com/sale-items.html?limit=20";

        $total_records = \App\Scrapping::deal_scraps("grabagun_count",$url);
        
        $cnt = 0;
        $newAdded = 0;
        if($total_records > 0)
        {
            $recordsPerPage = 20;
            $pages = ceil($total_records / $recordsPerPage);
            
            echo "<br />Total Pages: ".$pages;
            
            for($i = 1;$i<=$pages;$i++)
            {
                $url = "https://grabagun.com/sale-items.html?limit=20&p=$i";
                $rows = \App\Scrapping::deal_scraps("grabagun",$url);
                
                if(count($rows) > 0)
                {
                    foreach($rows as $row)
                    {
                        $name = trim($row['name']);
                        $link = trim($row['link']);
                        $linkMD5 = md5($link);

                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {

                            \DB::table("deals")
                            ->insert([
                                "source_id" => 8,
                                "title" => $name,
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "<br />".$cnt." records processed.";
                    }
                }
            }
        }

        return ['total' => $cnt,"new" => $newAdded];
    }
    public function scrapGrabagunDetailLinks(){        

        $rows = \DB::table("deals")
        ->where("source_id",8)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;            
            
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;            
            $res = \App\Scrapping::deal_scraps("grabagun_detail",$url);

            if(array_keys($res) > 0)
            {

                $source_id = 8;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");
                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

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
                $specifications = $res['specification'];    
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
            

            echo "\ncounter: ".$counter;            
        }        
    }

    public function scrapClassicfirearmsMasterLinks()
    {
        $url = "https://www.classicfirearms.com/product-specials";

        $total_records = \App\Scrapping::deal_scraps("classicfirearms_count",$url);
        
        $cnt = 0;
        $newAdded = 0;
        if($total_records > 0)
        {
            $recordsPerPage = 24;
            $pages = ceil($total_records / $recordsPerPage);
            
            echo "<br />Total Pages: ".$pages;
            
            for($i = 1;$i<=$pages;$i++)
            {
                $url = "https://www.classicfirearms.com/product-specials?p=$i";
                $rows = \App\Scrapping::deal_scraps("classicfirearms",$url);
                
                if(count($rows) > 0)
                {
                    foreach($rows as $row)
                    {
                        $name = trim($row['name']);
                        $link = trim($row['link']);
                        $linkMD5 = md5($link);

                        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
                        if(!$isExist)
                        {

                            \DB::table("deals")
                            ->insert([
                                "source_id" => 7,
                                "title" => $name,
                                "link" => $link,
                                "unique_md5" => $linkMD5,
                                "created_at" => date("Y-m-d H:i:s")
                            ]);

                            $newAdded++;
                        }

                        $cnt++;
                        echo "<br />".$cnt." records processed.";
                    }
                }
            }
        }

        return ['total' => $cnt,"new" => $newAdded];
    }
    public function scrapClassicfirearmsDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",7)
        ->get();


        $counter = 0;
        foreach ($rows as $row) 
        {
            $counter++;            
            
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;            
            $res = \App\Scrapping::deal_scraps("classicfirearms_detail",$url);
            
            // echo "<pre>";
            // print_r($res);
            // exit;
            
            if(array_keys($res) > 0)
            {

                $source_id = 7;
                $dataToUpdate = 
                [
                    "source_id" => $source_id,
                    "title" => $res["name"],
                    "out_of_stock" => $res["out_of_stock"],
                    "description" => $res["description"],
                    "qty_options" => !empty($res['qty_options']) ? json_encode($res['qty_options']):"",
                    "link" => $url,
                    "from_url" => $url,
                    "unique_md5" => $urlMD5,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "ext_date" => $res["ext_date"],
                    "save_price" => $res["saving_price"],                    
                ];                

                $dataToUpdate['updated_at'] = date("Y-m-d H:i:s");
                $dataToUpdate['last_visit_date'] = date("Y-m-d H:i:s");

                $deal_id = $mainID;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToUpdate);

                // Add Photos
                \DB::table("deal_photos")->where("deal_id",$deal_id)->delete();
                $images = $res['images'];    
                $tmpIMG = [];
                if(count($images) > 0)
                {
                    $dataToInsert = [];
                    foreach($images as $row)
                    {
                        $image = $row['image'];                        
                        if(!isset($tmpIMG[$image]))
                        {
                            $dataToInsert[] = [
                                "deal_id" => $deal_id,
                                "image_url" => $image,
                                "created_at" => date("Y-m-d H:i:s"),
                            ];
                            
                            $tmpIMG[$image] = 1;
                        }
                    }                    

                    \DB::table("deal_photos")->insert($dataToInsert);
                }

                // Add Specifications                
                \DB::table("deal_specifications")->where("deal_id",$deal_id)->delete();
                $specifications = $res['specification'];    
                $tmpOPT = [];
                if(count($specifications) > 0)
                {
                    $dataToInsert = [];
                    foreach($specifications as $row)
                    {
                        if(!isset($tmpOPT[$row['key']]))
                        {
                            $dataToInsert[] = 
                            [
                                "deal_id" => $deal_id,
                                "key" => $row['key'],
                                "value" => $row['value'],
                                "created_at" => date("Y-m-d H:i:s"),
                            ];
                            
                            $tmpOPT[$row['key']] = 1;
                        }
                    }                    

                    \DB::table("deal_specifications")->insert($dataToInsert);
                }               
            }
            
            echo "\ncounter: ".$counter;            
        }
    }


    public function handle()
    {        
        $type = $this->argument("type");

        $scriptStartTime = date("Y-m-d H:i:s");

        $content = [];

        if($type == "e-arms")
        {
            $cron_id = 1;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);

            $res = $this->scrapEArmsMasterLinks();
            $this->scrapEArmsDetailLinks();            
            $content = $res;
        }
        else if($type == "primaryarms")
        {
            $cron_id = 2;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapPrimaryArmsMasterLinks();
            $this->scrapPrimaryArmsDetailLinks();            
            $content = $res;
        }
        else if($type == "brownells")
        {
            $cron_id = 3;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapBrownellsMasterLinks();
            $this->scrapBrownellsDetailLinks();            
            $content = $res;            
        }
        else if($type == "sgammo")
        {
            $cron_id = 4;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapSgammoMasterLinks();
            $this->scrapSgammoDetailLinks();            
            $content = $res;
        }
        else if($type == "righttobear")
        {
            $cron_id = 5;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);

            $res = $this->scrapRighttoBearMasterLinks();
            $this->scrapRighttoBearDetailLinks();
            $content = $res;
        }
        else if($type == "preppergunshop")
        {
            $cron_id = 6;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapPreppergunshopMasterLinks();
            $this->scrapPreppergunshopDetailLinks();                    
            $content = $res;
        }
        else if($type == "palmettostatearmory")
        {
            $cron_id = 7;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapPsaMasterLinks();
            $this->scrapPsaDetailLinks();
            $content = $res;
        }
        else if($type == "classicfirearms")
        {
            $cron_id = 8;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                
            $res = $this->scrapClassicfirearmsMasterLinks();
            $this->scrapClassicfirearmsDetailLinks();
            $content = $res;
        }
        else if($type == "grabagun")
        {
            $cron_id = 9;            
            $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);                

            $res = $this->scrapGrabagunMasterLinks();
            $this->scrapGrabagunDetailLinks();
            $content = $res;
        }
        else
        {
            exit("Invalid cron type!");
        }

        $scriptEndTime = date("Y-m-d H:i:s");                
        storeCronLogs($scriptStartTime, $scriptEndTime, NULL, $content, 'Web Server', $cron_id, $mainLogID);
    }    
}