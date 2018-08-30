<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use ImageOptimizer;

class TestController extends Controller
{
    public function __construct()
    {
    }

    public function home()
    {
        $data = [];
        return view('test',$data);                
    }    

    public function listing($id)
    {
        $category = \App\Models\ProductCategory::find($id);
        if(!$category)
            abort(404);

        $ids = getChildrens($id);
        $ids[] = $id;
        $data['rows'] = \App\Models\Product::whereIn("product_category_id",$ids)->paginate(10);
        $data['page_title'] = $category->title;
        return view("listing", $data);
    }    

    public function scrapClassicfirearmsMasterLinks()
    {
        $url = "https://www.classicfirearms.com/product-specials";

        $total_records = \App\Scrapping::deal_scraps("classicfirearms_count",$url);
        
        $cnt = 0;
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
                        }

                        $cnt++;
                        echo "<br />".$cnt." records processed.";
                    }
                }
            }
        }
        
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
    
    
    public function scrapGrabagunMasterLinks()
    {
        $url = "https://grabagun.com/sale-items.html?limit=20";

        $total_records = \App\Scrapping::deal_scraps("grabagun_count",$url);
        
        $cnt = 0;
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
                        }

                        $cnt++;
                        echo "<br />".$cnt." records processed.";
                    }
                }
            }
        }
        
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

    public function index(Request $request)
    {   
        // $this->scrapGrabagunDetailLinks();
        // $this->scrapClassicfirearmsMasterLinks();
        $this->scrapClassicfirearmsDetailLinks();
        
        // $pathToImage = public_path().DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."2.jpg";
        // $pathToOptimizedImage = public_path().DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."BKP111_2.jpg";
        // // ImageOptimizer::optimize($pathToImage);        
        // ImageOptimizer::optimize($pathToImage, $pathToOptimizedImage);
        // exit("1 Test RUN 1");
    }            
}
