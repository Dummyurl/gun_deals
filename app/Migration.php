<?php

namespace App;
use Illuminate\Support\Facades\DB;
use App\Scrapping;
use App\Models\Product;

/**
 * Migration Class.
 *
 * @subpackage Migration class
 * @author     
 */
class Migration 
{
    public static function mapDeals()
    {
        $i = 0;
        $offset = 0;
        $limit = 100;
        while(true)
        {
            $rows = DB::table("deal_specifications as ds")
                    ->select("deals.id","ds.value as upc")
                    ->join("deals","deals.id","=","ds.deal_id")
                    ->whereRaw("TRIM(LOWER(ds.`key`)) = 'upc'")
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

                    $product = Product::where("upc_number",$upc)->first();
                    if($product)
                    {
                        \DB::table("deals")
                        ->where("id",$dealID)
                        ->update([
                            "product_id" => $product->id 
                        ]);
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

    public static function migrateMasterProducts()
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
                    $item_id = $row->item_id;
                    $category = $row->category;
                    $title = $row->title;
                    $link = $row->link;
                    $link_md5 = $row->link_md5;
                    $image = $row->image;
                    $thumb_image = $row->thumb_image;
                    $item_unique_id = $row->item_unique_id;
                    $msrp = $row->msrp;
                    $attr = $row->attr;
                    $upc_number = $brand = $model = "";

                    $dataToInsertAttr = [];

                    $notMapped = [
                        "UPC","Brand","Model","Item #","Description"
                    ];

                    echo "\nLink: ".$link;

                    if(!empty($attr))
                    {
                        $attr = json_decode($attr,1);
                        foreach($attr as $r)
                        {
                            $r['key'] = explode(":", $r['key']);
                            $r['key'] = trim($r['key'][0]);

                            if($r['key'] == "UPC:\u00a0" || $r['key'] == "UPC")
                            {
                                $upc_number = $r['val'];
                            }
                            else if($r['key'] == "Brand:\u00a0" || $r['key'] == "Brand")
                            {
                                $brand = $r['val'];
                            }
                            else if($r['key'] == "Model:\u00a0" || $r['key'] == "Model")
                            {
                                $model = $r['val'];
                            }

                            if(!in_array($r['key'], $notMapped))
                            {
                                $dataToInsertAttr[] = [
                                    "key" => $r['key'],
                                    "val" => $r['val']
                                ];
                            }
                        }                            
                    }

                    $title = $brand." ".$model;                    

                    $product_id = null;

                    if(!empty($upc_number))
                    $product_id = "GR-".$upc_number;
                    
                    // echo "\nUPC: ".$upc_number;
                    // echo "\nBrand: ".$brand;
                    // echo "\nModel: ".$model;                
                    // exit;                    
                    // continue;

                    $dataToInsert = 
                    [
                        "product_id" => $product_id,
                        "item_id" => $item_id,
                        "category" => $category,
                        "title" => $title,
                        "link" => $link,
                        "link_md5" => $link_md5,
                        "image" => $image,
                        "thumb_image" => $thumb_image,
                        "item_unique_id" => $item_id,
                        "brand" => $brand,
                        "model" => $model,
                        "upc_number" => $upc_number,
                        "msrp" => $msrp,                        
                        "created_at" => date("Y-m-d H:i:s")
                    ];

                    // print_r($dataToInsert);
                    // print_r($dataToInsertAttr);
                    // exit;

                    $product = \DB::table("products")
                                ->where("link",$link)
                                ->first();

                    if($product)
                    {
                        $productId = $product->id;

                        \DB::table("product_attributes")
                        ->where("id",$productId)
                        ->delete();

                        $dataToInsert['updated_at'] = $dataToInsert['created_at'];
                        unset($dataToInsert['created_at']);

                        \DB::table("products")
                        ->where("id",$productId)
                        ->update($dataToInsert);
                    }            
                    else
                    {
                        $productId = \DB::table("products")
                        ->insertGetId($dataToInsert);
                    }

                    if(count($dataToInsertAttr) > 0)
                    {
                        foreach($dataToInsertAttr as $r)
                        {
                            \DB::table("product_attributes")
                            ->insert([
                                "product_id" => $productId,
                                "keyname" => $r["key"],
                                "keyvalue" => $r["val"],
                            ]);
                        }
                    }

                    echo "\n".$i;
                    $i++;
                }
            }
            else
            {
                break;
            }
       }
    }
}

?>