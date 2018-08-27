<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrationDeals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:deals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create deals from master products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {        
        $this->createProducts();
        // $this->downloadProductImages();
        // $this->scrapGunDetails();

    }   

    public function scrapGunDetails()
    {
       $links = [];
       $sql = 'SELECT link FROM products WHERE product_id IN ("GR-047700852010","GR-725327616337","GR-736676067589","GR-736676072163","GR-787450431683","GR-806703064895");';

       $rows = \DB::select($sql); 
       foreach($rows as $row)
       {
            $links[] = $row->link;
            // $links[] = "https://www.galleryofguns.com/genie/Default.aspx?item=HG4068B-N&zipcode=49464";
       } 

       $i = 0;
       $offset = 0;
       $limit = 100;
       while(true)
       {
            $rows = \DB::table("galleryofguns")                    
                    ->whereIn("link",$links)
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

                    $data = \App\Scrapping::scrapGuns($link, "detail");
                    // print_r($data);
                    // exit;

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

    public function downloadProductImages()
    {
       $public_path = "/home/reaper/public_html/"; 
       $public_path = public_path();

       $i = 0;
       $offset = 0;
       $limit = 100;

       while(true)
       {
            $rows = \DB::table("products")
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

            $offset = $offset + $limit;

            if($rows && count($rows))
            {
                foreach($rows as $row)
                {                    
                    $url_to_image = $row->image;
                    if(!empty($url_to_image))
                    {
                        echo "\nImage: ".$url_to_image;
                        $my_save_dir = $public_path.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$row->id.DIRECTORY_SEPARATOR;
                        echo "\nPath: ".$my_save_dir;
                        makeDir($my_save_dir);
                        $filename = basename($url_to_image);
                        $complete_save_loc = $my_save_dir . $filename;
                        file_put_contents($complete_save_loc, file_get_contents($url_to_image));
                    }

                    $url_to_image = $row->thumb_image;
                    if(!empty($url_to_image))
                    {
                        echo "\nImage: ".$url_to_image;
                        $my_save_dir = $public_path.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$row->id.DIRECTORY_SEPARATOR;
                        echo "\nPath: ".$my_save_dir;
                        makeDir($my_save_dir);
                        $filename = "thumb_".basename($url_to_image);
                        $complete_save_loc = $my_save_dir . $filename;
                        file_put_contents($complete_save_loc, file_get_contents($url_to_image));
                    }
                    
                    $i++;
                    echo "\n".$i; 
                                         
                }   
            }
            else
            {
                break;
            }    
       }
    }

    public function createProducts()
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
                        // print_r($attr);
                        // exit;
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