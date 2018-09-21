<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapSourceUrl;
use App\Scrapping;
use App\Migration;

use App\Models\FinalProduct;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\DealerProduct;
use App\Models\ProductAttribute;
use App\Models\DealerProductAttribute;
use App\Models\FinalProductAttribute;


class MapProducts extends Command
{
    protected $mainAttrs = [];
    protected $categoryARR = [];
    protected $signature = 'migrate-products';
    protected $description = 'Migrate Baseline Products To Final Products.';

    public function __construct()
    {
        parent::__construct();        

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
            "rifle>>bolt action" => ProductCategory::$RIFLES_BOLT_ACTION,            
            "rifle>>lever action" => ProductCategory::$RIFLES_LEVER_ACTION,            
            "rifle>>pump action" => ProductCategory::$RIFLES_PUMP_ACTION,
            "rifle>>semi-auto" => ProductCategory::$RIFLES_SEMI_AUTO,
            "rifle>>single shot" => ProductCategory::$RIFLES_SINGLE_SHOT,
            "semi-auto pistol>>semi-auto" => ProductCategory::$HANDSGUN_SEMI_AUTO,
            "semi-auto pistol>>single action" => ProductCategory::$HANDSGUN_SPC_SINGLE_SHOT,
            "shotgun>>bolt action" => ProductCategory::$SHOTGUNS_BOLT_ACTION,
            "shotgun>>lever action" => ProductCategory::$SHOTGUNS_LEVER_ACTION,
            "shotgun>>over / under" => ProductCategory::$SHOTGUNS_OVER_UNDER,
            "shotgun>>pump action" => ProductCategory::$SHOTGUNS_PUMP_ACTION,
            "shotgun>>semi-auto" => ProductCategory::$SHOTGUNS_SEMI_AUTO,
            "shotgun>>side by side" => ProductCategory::$SHOTGUNS_SIDE_BY_SIDE,
            "shotgun>>single shot" => ProductCategory::$SHOTGUNS_SINGLE_SHOT,
            "specialty handgun>>lever action" => ProductCategory::$HANDSGUN_SPC_LEVER_ACTION,            
            "specialty handgun>>single shot" => ProductCategory::$HANDSGUN_SPC_SINGLE_SHOT,
            "revolver>>double / single action" => ProductCategory::$HANDSGUN_REVOLVER_DOUBLE_ACTION,
            "revolver>>double action only" => ProductCategory::$HANDSGUN_REVOLVER_DOUBLE_ACTION_ONLY,
            "revolver>>single action" => ProductCategory::$HANDSGUN_REVOLVER_SINGLE_ACTION,
        ];

        $categoryARR = [];

        foreach($tmp as $k => $v)
        {
            $categoryARR[strtolower($k)] = $tmp[$k];
        }        

        $this->categoryARR = $categoryARR;
    }

    public function mapProductCategory($type,$category = '')
    {
        $categoryARR = $this->categoryARR;
        $keyvalue = trim(strtolower($type));
        $categoryID = 0;

        $returnID = NULL;

        if(isset($categoryARR[$keyvalue]))
        {
            $categoryID = $categoryARR[$keyvalue];
            if($categoryID > 0)
            {
                $returnID = $categoryID;
            }
        }

        if($returnID > 0)
        {

        }
        else if(!empty($category))
        {
            $keyvalue = trim(strtolower($category));
            if(isset($categoryARR[$keyvalue]))
            {
                $categoryID = $categoryARR[$keyvalue];
                if($categoryID > 0)
                {
                    $returnID = $categoryID;
                }
            }
        }

        return $returnID;
    }


    public function fixkeynames()
    {
        $sql = 
        "
            SELECT pa.id,pa.keyname
            FROM products AS p
            JOIN product_attributes AS pa ON pa.`product_id` = p.`id`
            WHERE 1
        ";

        $rows = \DB::select($sql);
        $i = 0;
        foreach($rows as $row)
        {
            $id = $row->id;
            $keyName = trim($row->keyname);            
            $str = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',$keyName);
            $str = strtolower(trim($str));

            echo "\nKey =".$str;            

            \DB::table("product_attributes")
            ->where("id",$id)
            ->update(["keyname" => $str]);

            $i++;
            echo "\n$i";
        }
    }

    public function handle()
    {   
        $cron_id = 59;      
        $scriptStartTime = date("Y-m-d H:i:s");
        session(["total_count" => 0,"new_count" => 0]);      
        $mainLogID = storeCronLogs($scriptStartTime, NULL, NULL, NULL, 'Web Server', $cron_id);


        $keyNames = \DB::select
        ("
            SELECT TRIM(LOWER(pa.`keyname`)) as keyname
            FROM products AS p
            JOIN product_attributes AS pa ON pa.`product_id` = p.`id`
            WHERE (p.`source_id` = 15 OR p.`source_id` = 14) 
            GROUP BY TRIM(LOWER(pa.`keyname`));                    
        ");

        foreach($keyNames as $r)
        {
            $this->mainAttrs[$r->keyname] = $r->keyname;
        }

        $flag = true;

        $i = 0;
        $offset = 0;
        $limit = 100;

        while($flag)
        {
            $rows = Product::select("upc_number")
                    ->whereRaw("upc_number IS NOT NULL AND TRIM(upc_number) != ''")
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

            $offset = $offset + $limit;        

            if($rows && count($rows) > 0)
            {
                foreach($rows as $row)
                {                    
                    $upc_number = trim($row->upc_number);
                    $str = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',$upc_number);
                    
                    if(!empty($str))
                    {                        
                        $res = $this->getData($upc_number);
                        $this->insertFinalProduct($upc_number, $res);                        
                        $i++;

                        echo "\n$i";

                        $total_count = session("total_count");
                        $total_count++;
                        session(["total_count" => $total_count]);                                                            
                    }                    
                }
            }    
            else
            {
                $flag = false;
            }

        }            

        $content = ['total' => session("total_count"),"new" => session("new_count")];
        $scriptEndTime = date("Y-m-d H:i:s");
        storeCronLogs($scriptStartTime, $scriptEndTime, NULL, $content, 'Web Server', $cron_id, $mainLogID);        
    }

    public function getData($upc_number)
    {
        $mapID = 0;
        $fields = 
        [            
            "product_category_id" => "",
            "title" => "",
            "image" => "",
            "brand" => "",
            "model" => "",
            "mpn" => "",
            "upc_number" => $upc_number,
            "msrp" => ""
        ];

        $attrs = [];

        foreach($this->mainAttrs as $k => $v)
        {
            $attrs[$k] = "";
        }


        // first baseline = lipseys.com
        $source_id = 14;
        $obj = Product::where("upc_number",$upc_number)
               ->where("source_id",$source_id) 
               ->first();

        if($obj)
        {            
            $obj = $obj->toArray();
            
            if($mapID > 0)
            {

            }
            else
            {
                $mapID = $obj['id'];
            }


            foreach($fields as $key => $val)
            {
                if(isset($obj[$key]) && !empty($obj[$key]) && isset($fields[$key]) && empty($fields[$key]))
                {
                    $fields[$key] = $obj[$key];
                }
            }

            $productAttr = ProductAttribute::where("product_id",$obj['id'])->get();
            foreach($productAttr as $r)
            {
                $keyName = trim(strtolower($r->keyname));
                $keyvalue = trim(strtolower($r->keyvalue));
                if(isset($attrs[$keyName]) && empty($attrs[$keyName]))
                {
                    $attrs[$keyName] = $keyvalue;
                }
            }
        }       

        // second baseline = galleryofguns.com
        $source_id = 15;
        $obj = Product::where("upc_number",$upc_number)
               ->where("source_id",$source_id) 
               ->first();

        if($obj)
        {
            $obj = $obj->toArray();

            if($mapID > 0)
            {

            }
            else
            {
                $mapID = $obj['id'];
            }


            foreach($fields as $key => $val)
            {
                if(isset($obj[$key]) && !empty($obj[$key]) && isset($fields[$key]) && empty($fields[$key]))
                {
                    $fields[$key] = $obj[$key];
                }
            }

            $productAttr = ProductAttribute::where("product_id",$obj['id'])->get();
            foreach($productAttr as $r)
            {
                $keyName = trim(strtolower($r->keyname));
                $keyvalue = trim(strtolower($r->keyvalue));
                if(isset($attrs[$keyName]) && empty($attrs[$keyName]))
                {
                    $attrs[$keyName] = $keyvalue;
                }
            }            
        }       


        // third baseline
        $source_id = 12;
        $obj = DealerProduct::where("upc_number",$upc_number)
               ->where("source_id",$source_id) 
               ->first();

        if($obj)
        {
            $obj = $obj->toArray();

            foreach($fields as $key => $val)
            {
                if(isset($obj[$key]) && !empty($obj[$key]) && isset($fields[$key]) && empty($fields[$key]))
                {
                    $fields[$key] = $obj[$key];
                }
            }

            $productAttr = DealerProductAttribute::where("product_id",$obj['id'])->get();
            foreach($productAttr as $r)
            {
                $keyName = trim(strtolower($r->keyname));
                $keyvalue = trim(strtolower($r->keyvalue));
                if(isset($attrs[$keyName]) && empty($attrs[$keyName]))
                {
                    $attrs[$keyName] = $keyvalue;
                }
            }            
        }       

        if(isset($attrs['barrel']))
        {
            if(!empty($attrs['barrel']))
            {

            }
            else if(isset($attrs['barrel length']))
            {
                if(!empty($attrs['barrel length']))
                {
                    $attrs['barrel'] = $attrs['barrel length'];
                }
            }

            if(isset($attrs['barrel length']))
                unset($attrs['barrel length']);
        }
        

        return ['fields' => $fields, 'attrs' => $attrs, 'mapID' => $mapID];
    }

    public function getProductId($upc_number)
    {
        return "GR-".trim($upc_number);
    }

    public function insertFinalProduct($upc_number, $res)
    {
        $fields = $res['fields'];
        $attrs = $res['attrs'];
        $mapID = $res['mapID'];                      

        $fields['map_id'] = $mapID;

        if(isset($fields['product_category_id']))
            unset($fields['product_category_id']);


        $category = "";

        if(isset($attrs['type']) && !empty($attrs['type']) && isset($attrs['action']) && !empty($attrs['action']))
        {
            $category = $attrs['type'].">>".$attrs['action'];
            $fields['category'] = $category;
        }    

        $product_category_id = NULL;

        if(isset($attrs['type']) && !empty($attrs['type']))
        {
            $tmpID = $this->mapProductCategory($attrs['type'],$category);
            if($tmpID > 0)
            {
                $product_category_id = $tmpID;
            }
        }        

        $fields['product_category_id'] = $product_category_id;       

        $productID = $this->getProductId($upc_number);

        $checkProduct = FinalProduct::where("product_id",$productID)->first();
        if($checkProduct)
        {
              $id = $checkProduct->id;
              $checkProduct->update($fields);
        }
        else
        {            
              $fields['product_id'] = $productID;
              $product = new FinalProduct();
              $product = $product->create($fields);
              $id = $product->id;

                $new_count = session("new_count");
                $new_count++;
                session(["new_count" => $new_count]);            

        }

        // Delete old attr
        FinalProductAttribute::where("product_id",$id)->delete();

        $dataAttr = [];
        foreach($attrs as $key => $val)
        {
            if(!empty($key) && !empty($val))
            {
                $dataAttr[] = 
                [
                    'product_id' => $id,
                    'keyname' => $key,
                    'keyvalue' => $val,
                ];
            }
        }

        if(!empty($dataAttr))
        {
            $attrObj = new FinalProductAttribute;
            $attrObj->insert($dataAttr);
        }
    }
}