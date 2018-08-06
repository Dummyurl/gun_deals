<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:testdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron has been run successfully.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function scrapPsaMasterLinks()
    {
        $url = "https://palmettostatearmory.com/daily-deals-new.html";

        $total_records = \App\Scrapping::deal_scraps("palmettostatearmory_count",$url);
        
        $cnt = 0;
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
                        }

                        $cnt++;
                        echo "\n".$cnt." records processed.";
                    }
                }
            }
        }
    }    

    public function scrapPsaDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",1)
        ->get();
        // $rows = \DB::table("deals")->where("id",317)->get();
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

    public function scrapEArmsMasterLinks()
    {
        $flag = true;
        $i = 1;
        $cnt = 1;
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
    }

    public function scrapEArmsDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",2)
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;
            $res = \App\Scrapping::deal_scraps("earms_detail",$url);

            if(array_keys($res) > 0)
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

            $counter++;
            echo "\ncounter: ".$counter;            
        }

    }

    public function scrapPrimaryArmsMasterLinks()
    {
        $flag = true;
        $i = 1;
        $cnt = 1;
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
        $cnt = 1;
        $offset = 1;
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

            // print_r($rows);
            // exit;

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
    }

    public function scrapBrownellsDetailLinks()
    {
        $rows = \DB::table("deals")
        ->where("source_id",4)
        ->where("id",2504)        
        ->get();

        $counter = 0;
        foreach ($rows as $row) 
        {
            $url = $row->link;
            $urlMD5 = md5($row->link);
            $mainID = $row->id;

            echo "\nUrl: ".$url;
            
            $res = \App\Scrapping::deal_scraps("brownells_detail",$url);
            print_r($res);
            exit;

            if(array_keys($res) > 0)
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

    public function handle()
    {        
        //$this->scrapPsaMasterLinks();
        //$this->scrapPsaDetailLinks();

        //$this->scrapEArmsMasterLinks();
        //$this->scrapEArmsDetailLinks();

        //$this->scrapPrimaryArmsMasterLinks();
        //$this->scrapPrimaryArmsDetailLinks();

        // $this->scrapBrownellsMasterLinks();
        $this->scrapBrownellsDetailLinks();        
    }    
}