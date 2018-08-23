<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Custom;
use App\Scrapping;
use Goutte\Client;

class ScrapSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:sale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap Data For sale practise';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function scrapMasterLinks($type)
    {
         if($type == "dentaltown")
         {
            $url = "https://www.dentaltown.com/classifieds/category/2/for-sale-practice";
            $page = 1;
            $counter = 0;
            while(true)
            {
                $pageUrl = $url."?pg=$page";
                $rows = Scrapping::scrapSale("dentaltown_master",$pageUrl);
                echo "\nUrl: $pageUrl";
                if(is_array($rows) && count($rows) > 0)
                {
                    foreach($rows as $row)
                    {
                        $link = trim($row['link']);
                        $linkMD5 = md5($link);

                        $existObj = DB::table("sale_dental_masters")->where("link_md5",$linkMD5)->first();
                        if(!$existObj)
                        {
                            \DB::table("sale_dental_masters")
                            ->insert
                            (
                                [
                                    "category" => $type,
                                    "title" => trim($row['title']),
                                    "link" => $link,
                                    "link_md5" => $linkMD5,
                                    "created_at" => date("Y-m-d H:i:s")
                                ]
                            );
                        }

                        $counter++;
                        echo "\n$counter processed!";
                    }
                }
                else
                {
                    break;
                }

                $page++;
            }                        
         }   
    }

    public function scrapDetailLinks($type)
    {
        if($type == "dentaltown")
        {
            $rows = \DB::table("sale_dental_masters")
            ->where("category",$type)
            ->where("id",">",42)
            ->get();

            if($rows)
            {
                foreach($rows as $row)
                {
                    $link = $row->link;
                    // $link = "https://www.dentaltown.com/classifieds/details/132831/general-practice-for-sale-in-albany-new-york";
                    echo "\n ".$row->id." =>Url:".$link;
                    $data = Scrapping::scrapSale("dentaltown_detail",$link);
                    // print_r($data);
                    // exit;
                    if(count($data) > 0)
                    {
                        $ad_category = explode(">",$data['Category:']);
                        $ad_category = trim($ad_category[1]);

                        \DB::table("sale_dental_masters")
                        ->where("id",$row->id)
                        ->update
                        (
                        [
                            "ad_category" => $ad_category,
                            "ad_title" => trim($data['Classified Ad Title:']),
                            "ad_contact_name" => trim($data['Contact Name:']),
                            "price" => isset($data['Selling Price:']) ? trim($data['Selling Price:']):"",
                            "description" => addslashes($data['Description:']),
                            "ad_description" => addslashes($data['Description:']),
                            "ad_location" => trim($data['Location:']),
                            "ad_date_posted" => date("Y-m-d",strtotime(trim($data['Date Posted:']))),
                        ]
                    );
                    }
                    else
                    {
                        exit("request denied");
                    }
                }
            }
        }
    }

    public function handle()
    {        
        $type = "dentaltown";

        // $this->scrapMasterLinks($type);
        $this->scrapDetailLinks($type);

        $msg = "Command has been run !";
        $this->info($msg);
    }
}
