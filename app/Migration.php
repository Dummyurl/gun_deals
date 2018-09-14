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
    public static function createDeal($res)
    {
        $linkMD5 = $res['linkMD5'];
        $link = $res['link'];
        $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();
        $title = $res['name'];
        $upc_number = "";
        $qty = isset($res['qty']) ? $res['qty']:null;


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

        if(isset($res['out_of_stock']))
        {
            $dataToUpdate["out_of_stock"] = $res['out_of_stock'];
        }

        if(isset($res['url_id']))
        {
            $dataToUpdate["source_url_id"] = $res['url_id'];
        }


        $specifications = $res['specification'];            
        $mpn = "";
        $j = 0;
        if(count($specifications) > 0)
        {

            foreach($specifications as $row)
            {
                if(trim(strtolower($row['key'])) == "upc")
                {
                    $upc_number = $row['value'];
                }
                else if(trim(strtolower($row['key'])) == "mpn")
                {
                    $mpn = $row['value'];

                    if(isset($specifications[$j]))
                    unset($specifications[$j]);
                }

                $j++;
            }                    
        }               

        $unique_id = null;

        if(!empty($upc_number))
        {
            $unique_id = "GR-".$upc_number;
        }
        
        $dataToUpdate["mpn"] = $mpn;
        $dataToUpdate["unique_id"] = $unique_id;
        $dataToUpdate["upc_number"] = $upc_number;

        if(!$isExist)
        {
            $dataToUpdate["created_at"] = date("Y-m-d H:i:s");
            $deal_id = \DB::table("deals")->insertGetId($dataToUpdate);

            $new_count = session("new_count");
            $new_count++;
            session(["new_count" => $new_count]);            
        }
        else
        {
            $deal_id = $isExist->id;
            \DB::table("deals")
            ->where("id",$deal_id)
            ->update($dataToUpdate);            
        }

        // Deal Prices
        \DB::table("deal_prices")
        ->insert
        (
            [
                "deal_id" => $deal_id,
                "sale_price" => $res["special_price"],
                "base_price" => $res["old_price"],
                "date" => date("Y-m-d"),
                "qty" => $qty
            ]
        );


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

    public static function createProductFromMidUsa($res)
    {
        $description = $res['description'];
        $images = $res['images'];
        $specifications = $res['specification'];
        $products = $res['products'];
        $link = $res['link'];

        foreach($products as $product)
        {
            $linkMD5 = $res['linkMD5'];
            $title = $res['title'];

            $status = $product['status'];                        
            $upc_number = $product['upc'];
            $mpn = $product['mpn'];
            $mfg_name = $product['mfg_name'];
            $sale_price = $product['sale_price'];
            $old_price = $product['old_price'];
            $image_path = $product['image_path'];
            if(!empty($image_path))
            {
                $image_path = explode(".", $image_path);
                $image_path = $image_path[0];
            }
            $logoImage = $res['logoImage'];

            $res["special_price"] = $product['sale_price'];
            $res["old_price"] = $old_price;

            $qty = $product['qty'];            

            if(strtolower(trim($status)) == "available")
                $out_of_stock = 0;
            else
                $out_of_stock = 1;

            $attr = $product['attr'];
            $productImages = [];
            $productAttr = [];

            foreach($images as  $image)
            {
                $image = $image['image'];
                if(!empty($image_path))
                {
                    if(count($products) == 1)
                    {
                        $productImages[] = $image;
                    }
                    else if(strpos($image, $image_path) !== false)
                    {
                        $productImages[] = $image;
                    }
                }
            }

            foreach($attr as $r)
            {   
                $productAttr[] = [
                    "key" => $r['key'],
                    "value" => $r['value'],
                ];
            }

            foreach($specifications as $r)
            {   
                $productAttr[] = [
                    "key" => $r['key'],
                    "value" => $r['value'],
                ];
            }


            $unique_id = null;

            if(!empty($upc_number))
            {
                $unique_id = "GR-".$upc_number;
                $linkMD5 = md5($unique_id);
            }            

            $isExist = \DB::table("dealer_products")->where("link_md5",$linkMD5)->first();

            $dataToInsert = 
            [
                "source_id" => $res['source_id'],
                "breadcrumbs" => json_encode($res['categories']),
                "product_id" => $unique_id,
                "title" => $title,
                "description" => $res['description'],
                "link" => $link,
                "link_md5" => $linkMD5,                
                // "image" => $image,
                "upc_number" => $upc_number,
                "mpn" => $mpn,
                "mfg_name" => $mfg_name,
                "vendor_image" => $logoImage,
                "sale_price" => $sale_price,
                "base_price" => $old_price
            ];

            if($res['category_id'] > 0)
            $dataToInsert["product_category_id"] = $res['category_id'];

            if(isset($res['url_id']))
            {
                $dataToInsert["source_url_id"] = $res['url_id'];
            }

            $dataToUpdate["out_of_stock"] = $out_of_stock;

            if(isset($res['from_url']))
            {
                $dataToInsert["from_url"] = $res['from_url'];
            }

            $dataToInsert["last_visit_date"] = date("Y-m-d H:i:s");
            $dataToInsert['created_at'] = date("Y-m-d H:i:s");


            if($isExist)
            {
                $productId = $isExist->id;

                \DB::table("dealer_product_attributes")
                ->where("id",$productId)
                ->delete();

                $dataToInsert['updated_at'] = $dataToInsert['created_at'];
                unset($dataToInsert['created_at']);

                \DB::table("dealer_products")
                ->where("id",$productId)
                ->update($dataToInsert);


                \DB::table("dealer_product_attributes")
                ->where("product_id",$productId)
                ->delete();

                \DB::table("dealer_product_photos")
                ->where("product_id",$productId)
                ->delete();

            }
            else
            {
                $productId = \DB::table("dealer_products")
                ->insertGetId($dataToInsert);            
            }        

            \DB::table("dealer_product_prices")
            ->insert
            (
                [
                    "product_id" => $productId,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "qty" => $qty,
                    "date" => date("Y-m-d")
                ]
            );            

            if(count($productAttr) > 0)
            {
                foreach($productAttr as $r)
                {
                    \DB::table("dealer_product_attributes")
                    ->insert([
                        "product_id" => $productId,
                        "keyname" => $r["key"],
                        "keyvalue" => $r["value"],
                    ]);
                }
            }        

            if(count($productImages) > 0)
            {
                foreach($productImages as $image)
                {
                    \DB::table("dealer_product_photos")
                    ->insert([
                        "product_id" => $productId,
                        "image_url" => $image,
                        "created_at" => date("Y-m-d H:i:s")
                    ]);                
                }                    
            }        

        }
    }

    public static function createProduct($res)
    {
        $linkMD5 = $res['linkMD5'];
        $link = $res['link'];        
        $title = $res['title'];
        $description = isset($res['description']) ? $res['description']:"";
        $isExist = \DB::table("dealer_products")->where("link_md5",$linkMD5)->first();
        $specifications = $res['attr'];
        $qty = isset($res['qty']) ? $res['qty']:null;

        $upc_number = "";
        $MSRP = "";
        $mpn = "";
        $j = 0;
        if(count($specifications) > 0)
        {
            foreach($specifications as $row)
            {
                if(trim(strtolower($row['key'])) == "upc")
                {
                    $upc_number = $row['value'];
                }
                else if(trim(strtolower($row['key'])) == "msrp")
                {
                    $MSRP = $row['value'];
                }
                else if(trim(strtolower($row['key'])) == "mpn")
                {
                    $mpn = $row['value'];
                    if(isset($specifications[$j]))
                    {
                        unset($specifications[$j]);
                    }
                }
                $j++;
            }                    
        }               

        $unique_id = null;

        if(!empty($upc_number))
        $unique_id = "GR-".$upc_number;

        $image = "";

        $images = $res['images'];    

        $dataToInsert = 
        [
            "source_id" => $res['source_id'],
            "product_id" => $unique_id,
            "title" => $title,
            "description" => $description,
            "link" => $link,
            "link_md5" => $linkMD5,
            "image" => $image,
            "upc_number" => $upc_number,
            "sale_price" => $res["special_price"],
            "base_price" => $res["old_price"]
        ];

        if($res['category_id'] > 0)
        $dataToInsert["product_category_id"] = $res['category_id'];

        if(isset($res['url_id']))
        {
            $dataToInsert["source_url_id"] = $res['url_id'];
        }

        if(isset($res['out_of_stock']))
        {
            $dataToUpdate["out_of_stock"] = $res['out_of_stock'];
        }

        if(!empty($MSRP))
        {
            $dataToUpdate["msrp"] = $MSRP;
        }

        if(isset($res['from_url']))
        {
            $dataToInsert["from_url"] = $res['from_url'];
        }

        $dataToInsert["mpn"] = $mpn;
        $dataToInsert["last_visit_date"] = date("Y-m-d H:i:s");
        $dataToInsert['created_at'] = date("Y-m-d H:i:s");

        if($isExist)
        {
            $productId = $isExist->id;

            \DB::table("dealer_product_attributes")
            ->where("id",$productId)
            ->delete();

            $dataToInsert['updated_at'] = $dataToInsert['created_at'];
            unset($dataToInsert['created_at']);

            \DB::table("dealer_products")
            ->where("id",$productId)
            ->update($dataToInsert);

                \DB::table("dealer_product_attributes")
                ->where("product_id",$productId)
                ->delete();

                \DB::table("dealer_product_photos")
                ->where("product_id",$productId)
                ->delete();

        }
        else
        {
            $productId = \DB::table("dealer_products")
            ->insertGetId($dataToInsert);            
     
            $new_count = session("new_count");
            $new_count++;
            session(["new_count" => $new_count]);                 
        }        


        \DB::table("dealer_product_prices")
        ->insert
        (
            [
                "product_id" => $productId,
                "sale_price" => $res["special_price"],
                "base_price" => $res["old_price"],
                "date" => date("Y-m-d"),
                "qty" => $qty
            ]
        );


        if(count($specifications) > 0)
        {
            foreach($specifications as $r)
            {
                \DB::table("dealer_product_attributes")
                ->insert([
                    "product_id" => $productId,
                    "keyname" => $r["key"],
                    "keyvalue" => $r["value"],
                ]);
            }
        }        

        if(count($images) > 0)
        {
            foreach($images as $row)
            {
                $image = $row['image'];                        
                \DB::table("dealer_product_photos")
                ->insert([
                    "product_id" => $productId,
                    "image_url" => $image,
                    "created_at" => date("Y-m-d H:i:s")
                ]);                
            }                    
        }        

    }

    public static function createDealFromMidUsa($res)
    {
        $description = $res['description'];
        $images = $res['images'];
        $specifications = $res['specification'];
        $products = $res['products'];
        $link = $res['link'];

        foreach($products as $product)
        {
            $linkMD5 = $res['linkMD5'];
            $title = isset($product['title']) ? $product['title']:'';

            $status = $product['status'];                        
            $upc_number = $product['upc'];
            $mpn = $product['mpn'];
            $mfg_name = $product['mfg_name'];
            $sale_price = $product['sale_price'];
            $old_price = $product['old_price'];
            $image_path = $product['image_path'];
            if(!empty($image_path))
            {
                $image_path = explode(".", $image_path);
                $image_path = $image_path[0];
            }
            $logoImage = $res['logoImage'];

            $res["special_price"] = $product['sale_price'];
            $res["old_price"] = $old_price;

            $qty = $product['qty'];            

            if(strtolower(trim($status)) == "available")
                $out_of_stock = 0;
            else
                $out_of_stock = 1;

            $attr = $product['attr'];
            $productImages = [];
            $productAttr = [];

            foreach($images as  $image)
            {
                $image = $image['image'];
                if(!empty($image_path))
                {
                    if(count($products) == 1)
                    {
                        $productImages[] = $image;
                    }
                    else if(strpos($image, $image_path) !== false)
                    {
                        $productImages[] = $image;
                    }
                }
            }

            foreach($attr as $r)
            {   
                $productAttr[] = [
                    "key" => $r['key'],
                    "value" => $r['value'],
                ];
            }

            foreach($specifications as $r)
            {   
                $productAttr[] = [
                    "key" => $r['key'],
                    "value" => $r['value'],
                ];
            }


            $unique_id = null;

            if(!empty($upc_number))
            {
                $unique_id = "GR-".$upc_number;
                $linkMD5 = md5($unique_id);
            }            

            $isExist = \DB::table("deals")->where("unique_md5",$linkMD5)->first();

            $dataToInsert = 
            [
                "unique_id" => $unique_id,
                "breadcrumbs" => json_encode($res['categories']),
                "source_id" => $res['source_id'],
                "title" => $title,
                "link" => $link,            
                "unique_md5" => $linkMD5,   
                "upc_number" => $upc_number,             
                "description" => $res['description'],                
                "mpn" => $mpn,
                "mfg_name" => $mfg_name,
                "vendor_image" => $logoImage,
                "sale_price" => $sale_price,
                "base_price" => $old_price                
            ];

            if($res['category_id'] > 0)
            $dataToInsert["category_id"] = $res['category_id'];

            if(isset($res['url_id']))
            {
                $dataToInsert["source_url_id"] = $res['url_id'];
            }

            $dataToUpdate["out_of_stock"] = $out_of_stock;

            if(isset($res['from_url']))
            {
                $dataToInsert["from_url"] = $res['from_url'];
            }

            $dataToInsert["last_visit_date"] = date("Y-m-d H:i:s");            
            $dataToInsert["updated_at"] = date("Y-m-d H:i:s");                        

            if(!$isExist)
            {
                $dataToInsert["created_at"] = date("Y-m-d H:i:s");
                $deal_id = \DB::table("deals")->insertGetId($dataToInsert);
            }
            else
            {
                $deal_id = $isExist->id;

                \DB::table("deals")
                ->where("id",$deal_id)
                ->update($dataToInsert);            
            }

            \DB::table("deal_prices")
            ->insert
            (
                [
                    "deal_id" => $deal_id,
                    "sale_price" => $res["special_price"],
                    "base_price" => $res["old_price"],
                    "date" => date("Y-m-d"),
                    "qty" => $qty
                ]
            );

            // Add Photos
            \DB::table("deal_photos")->where("deal_id",$deal_id)->delete();
            
            if(count($productImages) > 0)
            {
                $dataToInsert = [];
                foreach($productImages as $image)
                {                    
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
            if(count($productAttr) > 0)
            {
                $dataToInsert = [];
                foreach($productAttr as $row)
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
    }

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

       $newAdded = 0;

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
                                ->where("link_md5",$link_md5)
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
                        $newAdded++;
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

       return ['total' => $i,"new" => $newAdded];
    }
}

?>