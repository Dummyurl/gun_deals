<?php

namespace App;
use Goutte\Client;

/**
 * Scrapping Class.
 *
 * @subpackage Scrapping class
 * @author     
 */
class Scrapping {

    public static function scrapGrabGunsProductCategory($pageURL)
    {
        echo "\n Page: ".$pageURL;
        $categoryUrls = self::deal_scraps("grabagun_categories",$pageURL);        
        return $categoryUrls;
    }

    public static function scrapMidwayUsaLinks($pageURL,$params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];
        $id = $params['id'];        

        $i = 0;
        $flag = true;
        while($flag)
        {
            echo "\nPage: ".$pageURL;
            $res = self::scrapMidwayUsaListing("master",$pageURL);
            $rows = isset($res['rows']) ? $res['rows']:[];
            $next_link = isset($res['next_link']) ? $res['next_link']:"";
            echo "\n Next Link: ".$next_link;

            if(count($rows) > 0)
            {
                // $rows = 
                // [
                //     "https://www.midwayusa.com/product/1018913183/imi-ammunition-338-lapua-magnum-250-grain-sierra-matchking-hollow-point-boat-tail",
                //     "https://www.midwayusa.com/product/1020539836/kriss-vector-sdp-g2-pistol-55-barrel-threaded-polymer",
                //     "https://www.midwayusa.com/product/108623/ar-stoner-ar-15-carbine-kit-with-complete-upper-assembly-556x45mm-nato-1-in-9-twist-16-barrel"
                // ];

                foreach($rows as $link)
                {
                    echo "\nDetail Link: ".$link;
                    $res = self::scrapMidwayUsaListing("detail",$link);                    

                    if(array_keys($res) > 0)
                    {
                        $linkMD5 = md5($link);                                
                        $res['link'] = $link;
                        $res['linkMD5'] = $linkMD5;                
                        $res['category_id'] = $category_id;
                        $res['source_id'] = $source_id;
                        $res['from_url'] = $scrap_url;
                        $res['url_id'] = $id;         

                        if($source_type == 1)
                        {
                            $res['title'] = $res['name'];
                            $res['attr'] = $res['specification'];
                            \App\Migration::createProductFromMidUsa($res);
                        }                        
                        else
                        {
                            \App\Migration::createDealFromMidUsa($res);
                        }                        
                    }                    
                    $i++;
                    echo "\n $i";
                }

                

                if(!empty($next_link))
                {
                    $pageURL = "https://www.midwayusa.com".$next_link;
                }
                else
                {
                    $flag = false;
                }
            }
            else
            {
                $flag = false;
            }
        }
    }

    public static function scrapMidwayUsaListing($type, $pageURL)
    {
        $client = new Client();
        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setClient($guzzleClient);
        $client->setHeader('user-agent', "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36");
        $options = array('request.options' => array(
                'proxy' => 'socks5://127.0.0.1:9050',
        ));        

        try
        {
            $crawler = $client->request('GET', $pageURL);

        }
        catch(\Symfony\Component\Debug\Exception\FatalErrorException $e)
        {
            return [];
        }       
        catch(\Exception $e)
        {
            return [];
        }

        if($type == "master")
        {
            if($crawler->filter(".shop-all")->count() > 0)
            {
                $pageURL = $crawler->filter(".shop-all")->first()->attr("href");
                $crawler = $client->request('GET', $pageURL);
            }

            session(["temp_rows" => []]);        
            session(['tmp_next_page' => '']);

            echo "\n\nCount: ".$crawler->filter("#prodlist li.product.list")->count();

            if($crawler->filter("#prodlist li.product.list")->count() > 0)
            {
                $crawler->filter("#prodlist li.product.list")->each(function($row){
                    if($row->filter("a")->count() > 0)
                    {
                        $link = trim($row->filter("a")->first()->attr("href"));
                        if(!empty($link))
                        {
                            $data = session("temp_rows");
                            $data[] = $link;
                            session(["temp_rows" => $data]);
                        }
                    }
                });
            }        

            if($crawler->filter("a.pagination-next")->count() > 0)
            {
                $link = trim($crawler->filter("a.pagination-next")->first()->attr("href"));
                session(['tmp_next_page' => $link]);
            }    

            return ['rows' => session("temp_rows"),"next_link" => session("tmp_next_page")];    
        }
        else
        {
            session(["rows" => []]);    

            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;
            $logoImage = "";


            if($crawler->filter("h1.heading-main.product-page-title")->count() > 0)
            {
                $name = trim($crawler->filter("h1.heading-main.product-page-title")->first()->text());
            }            

            if($crawler->filter("#overview.product-information.product-section-content .product-section-overview")->count() > 0)
            {
                $description = trim($crawler->filter("#overview.product-information.product-section-content .product-section-overview")->html());
            }            

            if($crawler->filter("#overview.product-information.product-section-content a.product-vendor-logo img")->count() > 0)
            {
                $logoImage = trim($crawler->filter("#overview.product-information.product-section-content a.product-vendor-logo img")->attr('data-ll-src'));
            }            



            session(["tmp_categories" => []]);
            if($crawler->filter("ol.breadcrumbs li")->count() > 0)
            {
               $crawler->filter("ol.breadcrumbs li")->each(function($li){
                   if(trim(strtolower($li->text())) != "home" && trim(strtolower($li->text())) != strtolower("HOT Deals"))
                   {
                       $categories = session("tmp_categories");
                       $categories[] = trim($li->text());
                       session(["tmp_categories" => $categories]);
                   }
               }); 
            }


            
            $mpn = "";
            session(["tmp_products" => []]);
            session(["tmp_product_attr" => []]);
            if($crawler->filter("script[type='application/ld+json']")->count() > 0)
            {
                $text = trim($crawler->filter("script[type='application/ld+json']")->html());
                $jsonData = json_decode($text,1);                                

                foreach($jsonData as $r)
                {
                    if(isset($r['mpn']))
                    $mpn = $r['mpn'];
                }

                if($crawler->filter('script[type="text/javascript"]')->count() > 0)
                {
                    $crawler->filter('script[type="text/javascript"]')->each(function($sc){

                        $input_str = trim($sc->html());

                        $pattern = "/window.productJson=([^;]*)/";
                        preg_match($pattern, $input_str, $matches);                        

                        if(isset($matches[1]))
                        {
                            $jsonData = json_decode($matches[1],1);
                            $products = [];
                            if(is_array($jsonData))
                            {
                                session(["tmp_products" => $jsonData]);
                            }
                        }

                        $pattern = "/window.familyAttributesJson=([^;]*)/";
                        preg_match($pattern, $input_str, $matches);                        

                        if(isset($matches[1]))
                        {
                            $jsonData = json_decode($matches[1],1);
                            $products = [];
                            if(is_array($jsonData))
                            {
                                session(["tmp_product_attr" => $jsonData]);
                            }
                        }

                    });
                }                
            }

            $jsonData = session("tmp_products");

            $products = [];
            if(is_array($jsonData))
            {
                // print_r($jsonData);
                // exit;
                
                foreach($jsonData as $r)
                {
                    $old_price = "";

                    $id = isset($r['ID']) ? $r['ID']:'';

                    if($crawler->filter("div[data-priceblock='".$id."'] .price-retail span")->count() > 0)
                    {
                        $old_price = trim($crawler->filter("div[data-priceblock='".$id."'] .price-retail span")->text());
                        $old_price = filterPrice($old_price);
                    }

                    $attr = 
                    [
                        "id" => $id,
                        "title" => isset($r['Name']) ? $r['Name']:'',
                        "status" => isset($r['Status']) ? $r['Status']:'',
                        "upc" => isset($r['Upc']) ? $r['Upc']:'',
                        "mpn" => isset($r['Sku']) ? $r['Sku']:'',
                        "mfg_name" => isset($r['Vendor']) ? $r['Vendor']:'',
                        "sale_price" => isset($r['Price']) ? $r['Price']:'',
                        "image_path" => isset($r['ImagePath']) ? $r['ImagePath']:'',
                        "old_price" =>$old_price,                        
                    ];

                    $qty = "";

                    $optAttr = [];

                    if(isset($r['Attributes']) && is_array($r['Attributes']))
                    {
                        foreach($r['Attributes'] as $ra)
                        {
                            if(isset($ra['Name']) && $ra['Name'] == 'Quantity')
                            {
                                 $qty = $ra['Value'];
                            }

                            if(isset($ra['IsDisplayable']) && $ra['IsDisplayable'])
                            {
                                $optAttr[] = ['key' => $ra['Name'],'value' => $ra['Value']];
                            }
                        }
                    }

                    $attr['qty'] = $qty;
                    $attr['attr'] = $optAttr;
                    
                    $products[] = $attr;
                }
            }

            session(["tmp_options" => []]);
            $options = [];
            $jsonData = session("tmp_product_attr");
            if(is_array($jsonData))
            {
                foreach($jsonData as $row)
                {
                    if(isset($row['IsDisplayable']) && $row['IsDisplayable'])
                    {
                        $options[] = ['key' => $row['Name'],'value' => $row['Value']];
                    }
                }
            }


            session(["tmp_images" => []]);     

            if($crawler->filter("#thumblist li")->count() > 0)
            {                
                    $crawler->filter("#thumblist li")->each(function($li){
                        if($li->filter("a")->count() > 0)
                        {
                            $image = trim($li->filter("a")->attr("href"));                       
                            if(!empty($image))
                            {
                                $tmp_images = session("tmp_images");
                                $tmp_images[] = ["image" => $image];
                                session(["tmp_images" => $tmp_images]);
                            }                                
                        }
                    });                    
            }      

            

            // if($crawler->filter("#product-attribute-specs-table tr")->count() > 0)
            // {
            //     $crawler->filter("#product-attribute-specs-table tr")->each(function($row){

            //         if($row->filter(".label")->count() > 0 && $row->filter(".data")->count() > 0)
            //         {
            //             $optionText = trim($row->filter(".label")->text());
            //             $optionValue = trim($row->filter(".data")->text());                        
            //             $optionText = rtrim($optionText,":");
            //             $optionValue = ltrim($optionValue,":");

            //             if(!empty($optionText) && !empty($optionValue))
            //             {
            //                 $tmp_options = session("tmp_options");
            //                 $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
            //                 session(["tmp_options" => $tmp_options]);
            //             }                        
            //         }    
            //     });
            // }            


            $rows = session("rows");            
            $rows['out_of_stock'] = 0;
            $rows['image'] = $base_image;
            $rows['logoImage'] = $logoImage;
            $rows['name'] = $name;
            $rows['mpn'] = $mpn;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['categories'] = session("tmp_categories");
            $rows['specification'] = $options;
            $rows['products'] = $products;            
            session(["rows" => $rows]);

            return session("rows");
        }
    }


    public static function scrapGrabGunsProductLinks($pageURL,$params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];
        $id = $params['id'];        

        $categoryUrls = self::scrapGrabGunsProductCategory($pageURL);        
        $links = [];
        if(count($categoryUrls) > 0)
        {
            foreach($categoryUrls as $url)
            {
                $link = $url['link'];                                
                $categoryUrls1 = self::scrapGrabGunsProductCategory($link);
                if(count($categoryUrls1) > 0)
                {
                    foreach($categoryUrls1 as $url)
                    {
                        $link = $url['link'];
                        $links[] = $link;
                    }
                }
                else
                {
                    $links[] = $link;
                }
            }
        }

        // $masterLinks = [];
        
        foreach($links as $link)
        {
            self::scrapGrabGunsListingLinks($link,$params);            
        }
    }

    public static function scrapGrabGunsListingLinks($pageURL,$params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];
        $id = $params['id'];        

        $url = $pageURL."?limit=20";
        echo "\nUrl: ".$url;
        $total_records = self::deal_scraps("grabagun_count",$url);
        $cnt = 0;
        $newAdded = 0;
        
        echo "\n Total Records: ".$total_records;
        if($total_records > 0)
        {
            $recordsPerPage = 20;
            $pages = ceil($total_records / $recordsPerPage);
            
            echo "<br />Total Pages: ".$pages;
            
            for($i = 1;$i<=$pages;$i++)
            {
                $url = $pageURL."?limit=20&p=$i";
                echo "\nPage: ".$url;
                $rows = self::deal_scraps("grabagun",$url);                
                foreach($rows as $row)
                {
                    $url = $row['link'];
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
                        $res['url_id'] = $id;         

                        if($source_type == 1)
                        {
                            $res['title'] = $res['name'];
                            $res['attr'] = $res['specification'];
                            \App\Migration::createProduct($res);
                        }                        
                        else
                        {
                            \App\Migration::createDeal($res);
                        }                        
                    }
                }
            }
        }    
    }

    public static function scrapPrepGunShopProductLinks($pageURL,$params)
    {
        $scrap_url = $params['scrap_url'];
        $source_type = $params['source_type'];
        $category_id = $params['category_id'];
        $source_id = $params['source_id'];
        $id = $params['id'];

        $cnt = 0;
        $url = $pageURL."?limit=150";
        $totalPages = \App\Scrapping::deal_scraps("preppergunshop_count",$url);
        echo "\nTotal Pages: ".$totalPages;        
        $newAdded = 0;  
        $mainLinks = [];

        for($j=1;$j<=$totalPages;$j++)
        {
            $url = $pageURL."?limit=150&p=$j";                            
            
            $rows = \App\Scrapping::deal_scraps("preppergunshop",$url);

            echo "\n$url";

            if(is_array($rows) && count($rows) > 0)
            {
                foreach($rows as $link)
                {
                    $link = trim($link);
                    $res = \App\Scrapping::deal_scraps("preppergunshop_detail",$link);

                    if(count($res) > 0)
                    {
                        $linkMD5 = md5($link);                                
                        $res['link'] = $link;
                        $res['linkMD5'] = $linkMD5;                
                        $res['category_id'] = $category_id;
                        $res['source_id'] = $source_id;
                        $res['from_url'] = $scrap_url;
                        $res['url_id'] = $id;
                        if($source_type == 1)
                        {
                            $res['title'] = $res['name'];
                            $res['attr'] = $res['specification'];
                            \App\Migration::createProduct($res);
                        }                        
                        else
                        {
                            \App\Migration::createDeal($res);
                        }                                                
                    }
                }
            }            
        }
    }

    public static function scrapAmmo($pageURL,$type)
    {
        $client = new Client();
        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setClient($guzzleClient);

        $options = array('request.options' => array(
                'proxy' => 'socks5://127.0.0.1:9050',
        ));        

        try
        {
            $crawler = $client->request('GET', $pageURL);

        }
        catch(\Symfony\Component\Debug\Exception\FatalErrorException $e)
        {
            return [];
        }       
        catch(\Exception $e)
        {
            return [];
        }

        session(["temp_rows" => []]);

        if($type == "handgun_master")
        {
            if($crawler->filter(".main-category-cols ul.col")->count() > 0)
            {
                $crawler->filter(".main-category-cols ul.col")->each(function($ul){

                    if($ul->filter("li")->count() > 0)
                    {
                        $ul->filter("li")->each(function($li){
                            if($li->filter("a")->count() > 0)
                            {
                                $label = trim($li->filter("a")->text());
                                $link = trim($li->filter("a")->attr("href"));

                                $data = session("temp_rows");
                                $data[] = ['title' => $label,"link" => $link];
                                session(["temp_rows" => $data]);                                           
                            }
                        });
                    }

                });
            }
        }
        else if($type == "handgun_master_1")
        {
            if($crawler->filter("#products-list li")->count() > 0){

               $crawler->filter("#products-list li")->each(function($li){     

                    if($li->filter("a")->count() > 0)
                    {
                        $link = trim($li->filter("a")->first()->attr("href"));
                        if(!empty($link))
                        {
                            $data = session("temp_rows");
                            $data[] = ['title' => "","link" => $link];
                            session(["temp_rows" => $data]);                                           
                        }
                    }
               });
            }
        }
        else if($type == "rimfire_master" || $type == "rimfire_master_2")
        {
            if($crawler->filter("#products-list li")->count() > 0){

               $crawler->filter("#products-list li")->each(function($li){     

                    if($li->filter("a")->count() > 0)
                    {
                        $link = trim($li->filter("a")->first()->attr("href"));
                        if(!empty($link))
                        {
                            $data = session("temp_rows");
                            $data[] = ['title' => "","link" => $link];
                            session(["temp_rows" => $data]);                                           
                        }
                    }
               });
            }

        }
        else if($type == "rimfire_master_1")
        {
            if($crawler->filter(".subcategories-with-filters tr")->count() > 0){

               $crawler->filter(".subcategories-with-filters tr")->each(function($li){     

                    if($li->filter("td")->count() > 0)
                    {
                        $li->filter("td")->each(function($td){
                            if($td->filter("a")->count() > 0)
                            {
                                $link = trim($td->filter("a")->first()->attr("href"));

                                if(!empty($link))
                                {
                                    $data = session("temp_rows");
                                    $data[] = ['title' => "","link" => $link];
                                    session(["temp_rows" => $data]);                                           
                                }
                            }
                        });
                    }
               });
            }

        }
        else if($type == "shotgun_master")
        {
            if($crawler->filter(".subcategories table tr")->count() > 0){

               $crawler->filter(".subcategories table tr")->each(function($li){     

                    if($li->filter("td")->count() > 0)
                    {
                        $li->filter("td")->each(function($td){
                            if($td->filter("a")->count() > 0)
                            {
                                $link = trim($td->filter("a")->first()->attr("href"));

                                if(!empty($link))
                                {
                                    $data = session("temp_rows");
                                    $data[] = ['title' => "","link" => $link];
                                    session(["temp_rows" => $data]);                                           
                                }
                            }
                        });
                    }
               });
            }

        }
        else if($type == "detail")
        {
            echo "\Count:".$crawler->filter("h1.product-name")->count();
            if($crawler->filter("h1.product-name")->count() > 0)
            {
                $title = trim($crawler->filter("h1.product-name")->text());
                $price = "";
                $desc = "";
                $sale_price = "";

                if($crawler->filter(".old-price .price")->count() > 0 && $crawler->filter(".special-price .price")->count() > 0)
                {
                    $price = trim($crawler->filter(".old-price .price")->first()->text());
                    $sale_price = trim($crawler->filter(".special-price .price")->first()->text());   
                }    
                else if($crawler->filter(".product-shop .regular-price")->count() > 0)
                {
                    $price = trim($crawler->filter(".product-shop .regular-price")->first()->text());   
                }

                if($crawler->filter(".product-section-details .std")->count() > 0)
                {
                    $desc = trim($crawler->filter(".product-section-details .std")->first()->html());   
                }


                session(["tmp_options" => []]);

                if($crawler->filter("#product-attribute-specs-table tr")->count() > 0)
                {
                    $crawler->filter("#product-attribute-specs-table tr")->each(function($row){

                        if($row->filter(".label")->count() > 0 && $row->filter(".data")->count() > 0)
                        {
                            $optionText = trim($row->filter(".label")->text());
                            $optionValue = trim($row->filter(".data")->text());                        
                            $optionText = rtrim($optionText,":");
                            $optionValue = ltrim($optionValue,":");

                            if(!empty($optionText) && !empty($optionValue))
                            {
                                $tmp_options = session("tmp_options");
                                $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                                session(["tmp_options" => $tmp_options]);
                            }                        
                        }    
                    });
                }       

                session(["tmp_images" => []]);

                if($crawler->filter("#imageGallery li")->count() > 0)
                {
                    $crawler->filter("#imageGallery li")->each(function($li){                        
                        $image = trim($li->attr("data-src"));                        
                        if(!empty($image))
                        {
                            $tmp_options = session("tmp_images");
                            $tmp_options[] = $image;
                            session(["tmp_images" => $tmp_options]);
                        }
                    });
                }             

                $attr = session("tmp_options");
                $images = session("tmp_images");

                $row = [];
                $row['title'] = $title;
                $row['price'] = $price;
                $row['sale_price'] = $sale_price;
                $row['desc'] = $desc;
                $row['attr'] = $attr;
                $row['images'] = $images;

                session(["temp_rows" => $row]);
            }
        }

        return session("temp_rows");
    }
    public static function scrapGuns($pageURL,$type)
    {
        $client = new Client();
        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setClient($guzzleClient);

        $options = array('request.options' => array(
                'proxy' => 'socks5://127.0.0.1:9050',
        ));

        $IS_TOR = \Config::get('app.IS_TOR');

        if ($IS_TOR == 1)
        {
            $crawler = $client->request('GET', $pageURL, $options);
        }
        else
        {
            $crawler = $client->request('GET', $pageURL);
        }
        
        session(["temp_rows" => []]);

        if($type == "master")
        {
            if($crawler->filter("ul.ThumbView li")->count() > 0)
            {
                $crawler->filter("ul.ThumbView li")->each(function($li){

                    if($li->filter(".ThumbViewTitle a")->count() > 0)
                    {
                        $title = trim($li->filter(".ThumbViewTitle a")->text());
                        $link = trim($li->filter(".ThumbViewTitle a")->attr("href"));

                        $linkTMP = explode("?", $link);
                        $itemID = 0;
                        if(isset($linkTMP[1]))
                        {   
                            $tmp = explode("&", $linkTMP[1]);
                            $itemID = str_replace("item=", "", $tmp[0]);
                        }

                        $data = session("temp_rows");
                        $data[] = ['title' => $title,"link" => $link,'itemID' => $itemID];
                        session(["temp_rows" => $data]);           
                    }
                });                  
            }
        }
        else if($type == "detail")
        {            
            session(["tmp_attr" => []]);
            if($crawler->filter("#tableIitemDetail tr")->count() > 0)
            {   
                $crawler->filter("#tableIitemDetail tr")->each(function($tr){
                    if($tr->filter("td")->count() == 2)
                    {

                        $key = trim($tr->filter("td")->first()->text());
                        $val = trim($tr->filter("td")->last()->text());

                        if(!empty($key) && !empty($val))
                        {
                            $data = session("tmp_attr");

                            $data[] = 
                            [
                                "key" => $key,
                                "val" => $val
                            ];

                            session(["tmp_attr" => $data]);
                        }
                    }    
                });
            }

            $image = "";
            if($crawler->filter("#LargeImage.ImagePopup img")->count() > 0)
            {
                $image = $crawler->filter("#LargeImage.ImagePopup img")->first()->attr("src");
                $image = trim($image);
            }

            $thumb_image = "";
            if($crawler->filter(".DetailPageWidths img.NavAppear")->count() > 0)
            {
                $thumb_image = $crawler->filter(".DetailPageWidths img.NavAppear")->first()->attr("src");
                $thumb_image = trim($thumb_image);
            }

            $itemID = "";
            if($crawler->filter("#ctl00_mainContent_mainContentControl_lblItemID")->count() > 0)
            {
                $itemID = $crawler->filter("#ctl00_mainContent_mainContentControl_lblItemID")->text();
                $itemID = trim($itemID);
            }

            $msrp = "";
            if($crawler->filter("#ctl00_mainContent_mainContentControl_lblMSRP")->count() > 0)
            {
                $msrp = $crawler->filter("#ctl00_mainContent_mainContentControl_lblMSRP")->text();
                $msrp = trim($msrp);
            }

            $attr = session("tmp_attr");

            $row['item'] = $itemID;
            $row['msrp'] = $msrp;
            $row['image'] = $image;
            $row['thumb_image'] = $thumb_image;
            $row['attr'] = $attr;
            session(["temp_rows" => $row]);
        }


        return session("temp_rows");
    }

    public static function deal_scraps($type, $pageURL)
    {
        session(["rows" => []]);

        $client = new Client();
        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setClient($guzzleClient);
        
        $client->setMaxRedirects(2);

        $options = array('request.options' => array(
                'proxy' => 'socks5://127.0.0.1:9050',
        ));

        $IS_TOR = \Config::get('app.IS_TOR');

        try
        {
            if ($IS_TOR == 1) 
            {
                $crawler = $client->request('GET', $pageURL, $options);
            } 
            else 
            {
                $crawler = $client->request('GET', $pageURL);
            }


        }
        catch(\Symfony\Component\Debug\Exception\FatalErrorException $e)
        {
            return [];
        }       
        catch(\Exception $e)
        {
            return [];
        }
        
        if($type == "classicfirearms_count")
        {
            if($crawler->filter('.pager .amount')->count() > 0)
            {
                $text = $crawler->filter('.pager .amount')->first()->text();
                
                $text = explode("of ", trim($text));

                if(isset($text[1]))
                {
                    $text[1] = str_replace("total","",$text[1]);
                    $text[1] = trim($text[1]);
                    return intval($text[1]);
                }
                else
                {
                    return 0;   
                }
            }

            return 0;
        }
        else if($type == "classicfirearms")
        {
            if($crawler->filter('ol.products-list .item')->count() > 0)
            {
                $crawler->filter('ol.products-list .item')->each(function ($ul) 
                {                        
                    if($ul->filter(".product-name a")->count() > 0)
                    {
                        $link = "https://www.classicfirearms.com".$ul->filter(".product-name a")->first()->attr("href");
                        
                        

                        if(!empty($link))
                        {
                            $rows = session("rows");
                            
                            $rows[] = 
                            [
                                "name" => "",
                                "link" => $link
                            ];    

                            session(["rows" => $rows]);
                        }                        
                    }
                });
            }            
            
        }
        else if($type == "classicfirearms_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;

            if($crawler->filter(".product-view .product-shop h1")->count() > 0)
            {
                $name = trim($crawler->filter(".product-view .product-shop h1")->first()->text());
            }            

            if($crawler->filter(".classic-product-view-section .std")->count() > 0)
            {
                $description = trim($crawler->filter(".classic-product-view-section .std")->html());
            }            
            else if($crawler->filter(".product-view .product-shop .short-description")->count() > 0)
            {
                $description = trim($crawler->filter(".product-view .product-shop .short-description")->html());
            }            

            if($crawler->filter(".product-options-bottom .old-price.aVersion .price")->count() > 0)
            {
                $old_price = trim($crawler->filter(".product-options-bottom .old-price.aVersion .price")->first()->text());
                
                $old_price = filterPrice($old_price);
            }            

            if($crawler->filter(".product-options-bottom .special-price.aVersion .price")->count() > 0)
            {
                $special_price = trim($crawler->filter(".product-options-bottom .special-price.aVersion .price")->first()->text());
                
                $special_price = filterPrice($special_price);
            }       

            session(["tmp_images" => []]);     

            if($crawler->filter("#carousel li a")->count() > 0)
            {                
                $crawler->filter("#carousel li a")->each(function ($row){                    
                    $image = $row->attr("href");                        
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }
                });
            }      

            session(["tmp_options" => []]);
            if($crawler->filter("#product-attribute-specs-table tr")->count() > 0)
            {
                $crawler->filter("#product-attribute-specs-table tr")->each(function($row){

                    if($row->filter("th")->count() > 0 && $row->filter("td")->count() > 0)
                    {
                        $optionText = trim($row->filter("th")->text());
                        $optionValue = trim($row->filter("td")->text());

                        if(!empty($optionText) && !empty($optionValue))
                        {
                            $tmp_options = session("tmp_options");
                            $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                            session(["tmp_options" => $tmp_options]);
                        }                        
                    }    
                });
            }

            $rows = session("rows");            
            $rows['out_of_stock'] = $crawler->filter(".product-view .availability.in-stock")->count() > 0 ? 0:1;
            $rows['image'] = $base_image;
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['review_count'] = $reviewCount;
            $rows['stars'] = $stars;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");            
            session(["rows" => $rows]);
            
        }
        if($type == "grabagun_count")
        {
            if($crawler->filter('.pager p.amount')->count() > 0)
            {
                if($crawler->filter('.pager .amount.amount--has-pages')->count() > 0)
                {
                    $text = $crawler->filter('.pager .amount.amount--has-pages')->first()->text();
                    
                    $text = explode("of ", trim($text));

                    if(isset($text[1]))
                        return intval($text[1]);
                    else
                        return 1;
                }
                else
                {
                    return 1;
                }
            }

            return 0;
        }
        else if($type == "grabagun_categories")
        {
            if($crawler->filter('#catList .item')->count() > 0)
            {
                $crawler->filter('#catList .item')->each(function ($ul) 
                {                        
                    if($ul->filter("a")->count() > 0)
                    {
                        $link = $ul->filter("a")->first()->attr("href");

                        if(!empty($link))
                        {
                            $rows = session("rows");
                            
                            $rows[] = 
                            [
                                "name" => "",
                                "link" => $link
                            ];    

                            session(["rows" => $rows]);
                        }                        
                    }
                });
            }            
        }
        else if($type == "grabagun")
        {
            if($crawler->filter('.products-grid .item')->count() > 0)
            {
                $crawler->filter('.products-grid .item')->each(function ($ul) 
                {                        
                    if($ul->filter("a")->count() > 0)
                    {
                        $link = $ul->filter("a")->first()->attr("href");

                        if(!empty($link))
                        {
                            $rows = session("rows");
                            
                            $rows[] = 
                            [
                                "name" => "",
                                "link" => $link
                            ];    

                            session(["rows" => $rows]);
                        }                        
                    }
                });
            }            
        }
        else if($type == "grabagun_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;


            if($crawler->filter(".product-essential .product-name h1")->count() > 0)
            {
                $name = trim($crawler->filter(".product-essential .product-name h1")->first()->text());
            }            

            if($crawler->filter(".product-essential  .short-description .std")->count() > 0)
            {
                $description = trim($crawler->filter(".product-essential  .short-description .std")->html());
            }            
           

            if($crawler->filter(".price-info .price-box .regular-price .price")->count() > 0)
            {
                $old_price = trim($crawler->filter(".price-info .price-box .regular-price .price")->first()->text());
                
                $old_price = filterPrice($old_price);
            }            



            session(["tmp_images" => []]);     

            if($crawler->filter(".product-img-box .product-image-gallery #image-main")->count() > 0)
            {                
                    $image = $crawler->filter(".product-img-box .product-image-gallery #image-main")->first()->attr("src");                        
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }
            }      

            session(["tmp_options" => []]);

            if($crawler->filter("#product-attribute-specs-table tr")->count() > 0)
            {
                $crawler->filter("#product-attribute-specs-table tr")->each(function($row){

                    if($row->filter(".label")->count() > 0 && $row->filter(".data")->count() > 0)
                    {
                        $optionText = trim($row->filter(".label")->text());
                        $optionValue = trim($row->filter(".data")->text());                        
                        $optionText = rtrim($optionText,":");
                        $optionValue = ltrim($optionValue,":");

                        if(!empty($optionText) && !empty($optionValue))
                        {
                            $tmp_options = session("tmp_options");
                            $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                            session(["tmp_options" => $tmp_options]);
                        }                        
                    }    
                });
            }            



            $rows = session("rows");            
            $rows['out_of_stock'] = $crawler->filter(".product-info .availability.in-stock")->count() > 0 ? 0:1;
            $rows['image'] = $base_image;
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");            
            session(["rows" => $rows]);
            
        }
        else if($type == "palmettostatearmory_count")
        {
            if($crawler->filter('.pager p.amount')->count() > 0)
            {
                $text = $crawler->filter('.pager p.amount')->first()->text();
                $text = explode("of ", trim($text));

                if(isset($text[1]))
                    return intval($text[1]);
                else
                    return 0;
            }

            return 0;
        }
        else if($type == "palmettostatearmory")
        {
            if($crawler->filter('#amshopby-page-container .products-grid')->count() > 0)
            {
                $crawler->filter('#amshopby-page-container .products-grid')->each(function ($ul) 
                {                        
                    if($ul->filter("li.item")->count() > 0)
                    {
                        $ul->filter("li.item")->each(function ($li){

                            $rows = session("rows");

                            $name = $li->filter(".product-name a")->text();
                            $link = $li->filter(".product-name a")->attr("href");

                            if(!empty($name))
                            {
                                $rows[] = 
                                [
                                    "name" => $name,
                                    "link" => $link
                                ];    

                                session(["rows" => $rows]);
                            }
                        });
                    }
                });
            }
        }
        else if($type == "palmettostatearmory_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;

            if($crawler->filter(".daily-deal-expires")->count() > 0)
            {
                $ext_date = $crawler->filter(".daily-deal-expires")->text();
            }

            if($crawler->filter("#amasty_gallery a")->count() > 0)
            {
                if($crawler->filter("#amasty_gallery a img")->count() > 0)
                {
                    $base_image = $crawler->filter("#amasty_gallery a img")->first()->attr("src");
                }
            }


            if($crawler->filter(".product-name h1")->count() > 0)
            {
                $name = trim($crawler->filter(".product-name h1")->first()->text());
            }            

            if($crawler->filter(".box-collateral.box-description .std")->count() > 0)
            {
                $description = trim($crawler->filter(".box-collateral.box-description .std")->html());
            }            
            else if($crawler->filter(".product-collateral .tab-container .std")->count() > 0)
            {
                $description = trim($crawler->filter(".product-collateral .tab-container .std")->html());
            }            

            if($crawler->filter(".TTreviewSummary .TTreviewCount")->count() > 0)
            {
                $reviewCount = trim($crawler->filter(".TTreviewSummary .TTreviewCount")->first()->text());
                $reviewCount = intval($reviewCount);
            }            

            if($crawler->filter("span#TTreviewSummaryAverageRating")->count() > 0)
            {
                $tmp = trim($crawler->filter("span#TTreviewSummaryAverageRating")->first()->text());
                $tmp = explode("/", $tmp);                
                $stars = trim($tmp[0]);
            }            

            if($crawler->filter(".old-price .price-value")->count() > 0)
            {
                $old_price = trim($crawler->filter(".old-price .price-value")->first()->text());
            }            

            if($crawler->filter(".special-price .price-value")->count() > 0)
            {
                $special_price = trim($crawler->filter(".special-price .price-value")->first()->text());
            }       
            else if($crawler->filter(".price-box-bundle .price-box .price")->count() > 0)
            {                
                $special_price = $crawler->filter(".price-box-bundle .price-box .price")->text();
                $special_price = str_replace("$", "", $special_price);
            }

            session(["tmp_images" => []]);     

            if($crawler->filter("#amasty_gallery a")->count() > 0)
            {                
                $crawler->filter("#amasty_gallery a")->each(function ($row){                    
                    $image = $row->attr("data-zoom-image");                        
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }
                });
            }      

            session(["tmp_options" => []]);
            if($crawler->filter(".std .attribute_div")->count() > 0)
            {
                $crawler->filter(".std .attribute_div")->each(function($row){

                    if($row->filter("span")->count() > 0)
                    {
                        $optionText = trim($row->filter("span")->text());
                        $optionValue = trim($row->text());
                        $optionText = rtrim($optionText,":");
                        $optionValue = str_replace($optionText, "", $optionValue);
                        $optionValue = trim($optionValue);
                        $optionValue = ltrim($optionValue,": ");
                        $optionValue = ltrim($optionValue,":");

                        if(!empty($optionText) && !empty($optionValue))
                        {
                            $tmp_options = session("tmp_options");
                            $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                            session(["tmp_options" => $tmp_options]);
                        }                        
                    }    
                });
            }

            if($crawler->filter(".std .short_description2")->count() > 0)
            {
                $crawler->filter(".std .short_description2 ul li")->each(function($row){
                    if($row->filter("strong")->count() > 0)
                    {
                        $optionText = trim($row->filter("strong")->text());
                        $optionText = rtrim($optionText,":");                    
                        $optionValue = trim($row->text());
                        $optionValue = str_replace($optionText, "", $optionValue);
                        $optionValue = trim($optionValue);
                        $optionValue = ltrim($optionValue,": ");
                        $optionValue = ltrim($optionValue,":");

                        if(!empty($optionText) && !empty($optionValue))
                        {
                            $tmp_options = session("tmp_options");
                            $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                            session(["tmp_options" => $tmp_options]);
                        }                        
                    }
                    else
                    {
                        $text = trim($row->text());
                        $tmp = explode(":", $text);

                        if(isset($tmp[0]) && isset($tmp[1]))
                        {
                            $optionText = trim($tmp[0]); 
                            $optionValue = trim($tmp[1]); 
                            if(!empty($optionText) && !empty($optionValue))
                            {
                                $tmp_options = session("tmp_options");
                                $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                                session(["tmp_options" => $tmp_options]);
                            }                                                    
                        }
                    }
                });

                if($crawler->filter(".std .short_description2 p span")->count() > 0)
                {
                    $crawler->filter(".std .short_description2 p span")->each(function($span){
                            $text = trim($span->text());
                            $tmp = explode(":", $text);

                            if(isset($tmp[0]) && isset($tmp[1]))
                            {
                                $optionText = trim($tmp[0]); 
                                $optionValue = trim($tmp[1]); 
                                if(!empty($optionText) && !empty($optionValue))
                                {
                                    $tmp_options = session("tmp_options");
                                    $tmp_options[] = ["key" => $optionText,"value" => $optionValue];
                                    session(["tmp_options" => $tmp_options]);
                                }                                                    
                            }                                                
                    });
                }
            }



            $rows = session("rows");            
            $rows['out_of_stock'] = $crawler->filter(".availability.out-of-stock")->count() > 0 ? 1:0;
            $rows['image'] = $base_image;
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['review_count'] = $reviewCount;
            $rows['stars'] = $stars;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");            
            session(["rows" => $rows]);

            // Get Photo Gallery

        }
        else if($type == "e-arms")
        {
            if($crawler->filter(".productGrid li.product")->count() >0 )
            {
                $crawler->filter(".productGrid li.product")->each(function($li){

                        $rows = session("rows");

                        $link = $li->filter(".card-figure a")->first()->attr("href");

                        if(!empty($link))
                        {
                            $rows[] = $link;    
                            session(["rows" => $rows]);
                        }
                });
            }
        }
        else if($type == "earms_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;

            session(["tmp_options" => []]);


            if($crawler->filter(".productView-title")->count() > 0)
            {
                $name = trim($crawler->filter(".productView-title")->text());
            }

            if($crawler->filter("#tab-description")->count() > 0)
            {
                $description = trim($crawler->filter("#tab-description")->html());
            }

            if($crawler->filter('meta[property="product:price:amount"]')->count() > 0)
            {
                $special_price = trim($crawler->filter('meta[property="product:price:amount"]')->attr('content'));
            }

            if($crawler->filter('meta[property="og:price:standard_amount"]')->count() > 0)
            {
                $old_price = trim($crawler->filter('meta[property="og:price:standard_amount"]')->attr('content'));
            }
            else if($crawler->filter('meta[property="product:price:amount"]')->count() > 0)
            {
                $old_price = trim($crawler->filter('meta[property="product:price:amount"]')->attr('content'));
            }

            session(["tmp_images" => []]);     

            if($crawler->filter(".productView-thumbnails .productView-thumbnail")->count() > 0)
            {                
                $crawler->filter(".productView-thumbnails .productView-thumbnail")->each(function ($row){                    

                    $image = $row->filter("a")->first()->attr("data-image-gallery-zoom-image-url");
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }
                });
            }

            if($crawler->filter(".productView-info-value[data-product-sku]")->count() > 0)
            {                
                $sku = $crawler->filter(".productView-info-value[data-product-sku]")->first()->text();
                $tmp_options = session("tmp_options");
                $tmp_options[] = ["key" => "SKU", "value" => $sku];
                session(["tmp_options" => $tmp_options]);                
            }

            if($crawler->filter(".productView-info-value[data-product-upc]")->count() > 0)
            {                
                $upc = $crawler->filter(".productView-info-value[data-product-upc]")->first()->text();
                $tmp_options = session("tmp_options");
                $tmp_options[] = ["key" => "UPC", "value" => $upc];
                session(["tmp_options" => $tmp_options]);                                
            }

            if($crawler->filter(".productView-info")->count() > 0)
            {           
                if($crawler->filter(".productView-info .productView-info-name")->count() > 0)
                {
                    $txt  = $crawler->filter(".productView-info .productView-info-name")->last()->text();
                    if(trim($txt) == "Type:")
                    {
                        if($crawler->filter(".productView-info .productView-info-value")->count() > 0)
                        {
                            $txt  = $crawler->filter(".productView-info .productView-info-value")->last()->text();
                            if(trim($txt) != '')
                            {
                                $tmp_options = session("tmp_options");
                                $tmp_options[] = ["key" => "Type", "value" => $txt];
                                session(["tmp_options" => $tmp_options]);
                            }
                        }
                    }
                }     
                
            
            }


            $out_of_stock = 0;

            if($crawler->filter(".alertBox-message")->count() > 0)
            {
                $tmp = $crawler->filter(".alertBox-message")->first()->text();
                if(strpos($tmp, 'Out of Stock') !== false)
                {
                    $out_of_stock = 1;
                }
            }

                        
            session(["tmp_categories" => []]);
            if($crawler->filter("ul.breadcrumbs li.breadcrumb")->count() > 0)
            {
               $crawler->filter("ul.breadcrumbs li.breadcrumb")->each(function($li){
                   if(trim(strtolower($li->text())) != "home" && trim(strtolower($li->text())) != strtolower("HOT Deals"))
                   {
                       $categories = session("tmp_categories");
                       $categories[] = trim($li->text());
                       session(["tmp_categories" => $categories]);
                   }
               }); 
            }

            $rows = session("rows");            
            $rows['out_of_stock'] = $out_of_stock;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");            
            $rows['categories'] = session("tmp_categories");
            session(["rows" => $rows]);
        }
        else if($type == "primaryarms")
        {
            if($crawler->filter(".facets-items-collection-view-cell-span3")->count() >0 )
            {
                $crawler->filter(".facets-items-collection-view-cell-span3")->each(function($li){

                        $rows = session("rows");

                        $link = "https://www.primaryarms.com".$li->filter("a")->first()->attr("href");

                        if(!empty($link))
                        {
                            $rows[] = $link;    
                            session(["rows" => $rows]);
                        }
                });
            }
        }
        else if($type == "primaryarms_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;

            session(["tmp_options" => []]);


            if($crawler->filter(".item-details-content-header-title")->count() > 0)
            {
                $name = trim($crawler->filter(".item-details-content-header-title")->text());
            }

            if($crawler->filter("#item-details-content-container-0")->count() > 0)
            {
                $description = trim($crawler->filter("#item-details-content-container-0")->html());
            }

            if($crawler->filter('.item-views-price-lead-sale')->count() > 0)
            {
                $special_price = trim($crawler->filter('.item-views-price-lead-sale')->text());
                $special_price = str_replace("$", "", $special_price);
                $special_price = trim($special_price);
                $special_price = floatval($special_price);
            }

            if($crawler->filter('.item-views-price-old')->count() > 0)
            {
                $old_price = trim($crawler->filter('.item-views-price-old')->text());
                $old_price = str_replace("$", "", $old_price);
                $old_price = trim($old_price);
                $old_price = floatval($old_price);
            }

            session(["tmp_images" => []]);     

            if($crawler->filter(".item-details-image-gallery .bxslider li")->count() > 0)
            {                
                $crawler->filter(".item-details-image-gallery .bxslider li")->each(function ($row){                    

                    if($row->filter("img")->count() > 0)
                    {
                        $image = $row->filter("img")->first()->attr("src");
                        if(!empty($image))
                        {
                            $tmp_images = session("tmp_images");
                            $tmp_images[] = ["image" => $image];
                            session(["tmp_images" => $tmp_images]);
                        }
                    }
                });
            }
            else if($crawler->filter(".item-details-image-gallery-detailed-image img")->count() > 0)
            {
                $image = $crawler->filter(".item-details-image-gallery-detailed-image img")->first()->attr("src");
                if(!empty($image))
                {
                    $tmp_images = session("tmp_images");
                    $tmp_images[] = ["image" => $image];
                    session(["tmp_images" => $tmp_images]);
                }
            }

            if($crawler->filter(".item-details-attributes #attribute-row")->count() > 0)
            {
                $crawler->filter(".item-details-attributes #attribute-row")->each(function($row){

                    if($row->filter("#attribute-name")->count() > 0)
                    {
                        $name = $row->filter("#attribute-name")->text();
                        $val = $row->filter("#attribute-value")->text();

                        if(!empty($name) && !empty($val))
                        {
                            $tmp_options = session("tmp_options");
                            $tmp_options[] = ["key" => $name, "value" => $val];
                            session(["tmp_options" => $tmp_options]);
                        }                    
                    }
                });
            }            

            $out_of_stock = 0;
            if($crawler->filter(".item-views-stock-msg-out")->count() > 0)
            {
                $out_of_stock = 1;
            }
            
            if($old_price == 0 && $special_price == 0 && $crawler->filter(".item-views-price-lead-p .item-views-price-lead")->count() > 0)
            {
                $tmp = $crawler->filter(".item-views-price-lead-p .item-views-price-lead")->first()->text();
                $old_price = filterPrice($tmp);
            }
                        
            $rows = session("rows");            
            $rows['out_of_stock'] = $out_of_stock;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");
            session(["rows" => $rows]);            
        }   
        else if($type == "brownells")
        {
            if($crawler->filter(".media.listing")->count() >0 )
            {
                $crawler->filter(".media.listing")->each(function($li){

                        $rows = session("rows");

                        $link = "https://www.brownells.com".$li->filter("a")->first()->attr("href");
                        $tmp = explode("?", $link);
                        $link = $tmp[0];
                        
                        if(!empty($link))
                        {
                            $rows[] = $link;    
                            session(["rows" => $rows]);
                        }
                });
            }
        }
        else if($type == "brownells_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;

            session(["tmp_options" => []]);


            if($crawler->filter("h1.mbm")->count() > 0)
            {
                $name = trim($crawler->filter("h1.mbm")->text());
            }

            if($crawler->filter("#producttabDescription")->count() > 0)
            {
                $description = trim($crawler->filter("#producttabDescription")->html());
            }

            session(["tmp_images" => []]);     

            if($crawler->filter(".bxslider li")->count() > 0)
            {                
                $crawler->filter(".bxslider li")->each(function ($row){                    

                    if($row->filter("img")->count() > 0)
                    {
                        $image = $row->filter("img")->first()->attr("src");
                        if(!empty($image))
                        {
                            $tmp_images = session("tmp_images");
                            $tmp_images[] = ["image" => $image];
                            session(["tmp_images" => $tmp_images]);
                        }
                    }
                });
            }

            session(["tmp_options" => []]);
            if($crawler->filter("#rawData")->count() > 0)
            {
                $jsonData = trim($crawler->filter("#rawData")->html());
                $jsonData = json_decode($jsonData,1);
                if(is_array($jsonData))
                {
                    foreach($jsonData as $key => $val)
                    {
                        if(isset($jsonData[$key]['LoadSKUSpecificInfo']['PriceText']) && $old_price == 0)
                        {
                            $html = $jsonData[$key]['LoadSKUSpecificInfo']['PriceText'];
                            preg_match_all("/<span class='strike'>(.*?)<\/span>/s", $html, $matches);

                            if(isset($matches[1][0]))
                            {
                                $old_price = trim($matches[1][0]);
                                $old_price = filterPrice($old_price);
                            }
                        }
                        
                        if(isset($jsonData[$key]['Attributes']))
                        {
                            foreach($jsonData[$key]['Attributes'] as $r)
                            {
                                $key = $r['AttributeName'];
                                $val = $r['AttributeValue'];

                                if(!empty($key) && !empty($val))
                                {
                                    $tmp_options = session("tmp_options");
                                    $tmp_options[] = ["key" => $key, "value" => $val];
                                    session(["tmp_options" => $tmp_options]);
                                }                                                    
                            }
                        }                        
                    }
                }
            }            

            $out_of_stock = 0;
            if($crawler->filter("#rawData")->count() > 0)
            {
                $jsonData = trim($crawler->filter("#generalData")->html());
                $jsonData = json_decode($jsonData,1);
                if(is_array($jsonData))
                {
                    if(isset($jsonData['Sale']) && $jsonData['Sale'])
                    {
                        $out_of_stock = 0;
                    }
                    else
                    {
                        $out_of_stock = 1;
                    }
                }
            }
            
            
            if($crawler->filter('#price.prc p.prc')->count() > 0)
            {
                $special_price = trim($crawler->filter('#price.prc p.prc')->text());
                $special_price = filterPrice($special_price);
            }            

            if($old_price == 0)
            {
                if($crawler->filter('#priceContainer .prc p')->count() > 0)
                {
                    $old_price = trim($crawler->filter('priceContainer .prc p')->text());
                    $old_price = filterPrice($old_price);
                }
            }       
            
            if($old_price == 0 && $special_price == 0)
            {
                if($crawler->filter('#priceContainer .prc .sale')->count() > 0)
                {
                    $special_price = trim($crawler->filter('#priceContainer .prc .sale')->first()->text());
                    $special_price = filterPrice($special_price);
                }

                if($crawler->filter('#priceContainer .prc .strike')->count() > 0)
                {
                    $old_price = trim($crawler->filter('#priceContainer .prc .strike')->first()->text());
                    $old_price = filterPrice($old_price);
                }                
            }
            
            if($crawler->filter(".wrap#wrap")->count() > 1)
            {
               if($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer .prc .sale")->count())
               {
                    $special_price = trim($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer .prc .sale")->text());
                    $special_price = filterPrice($special_price);
                    
                    if($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer .prc .strike")->count())
                    {
                        $old_price = trim($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer .prc .strike")->text());
                        $old_price = filterPrice($old_price);
                    }                                   
               }
               else if($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer p.prc")->count())
               {
                  $old_price = trim($crawler->filter(".wrap#wrap")->first()->filter("#priceContainer p.prc")->text()); 
                  $old_price = filterPrice($old_price);
                  $special_price = 0;                  
               } 
            }
            
            if($old_price == 0 && $special_price == 0 && $crawler->filter("#priceContainer p.prc")->count() > 0)
            {
                $tmp = trim($crawler->filter("#priceContainer p.prc")->first()->text());
                $old_price = filterPrice($tmp);
            }
            
            
            session(["tmp_categories" => []]);
            if($crawler->filter(".breadCrumb.mbl ul li")->count() > 0)
            {
               $crawler->filter(".breadCrumb.mbl ul li")->each(function($li){
                   if(trim(strtolower($li->text())) != "home")
                   {
                       $categories = session("tmp_categories");
                       $categories[] = $li->text();
                       session(["tmp_categories" => $categories]);
                   }
               }); 
            }
            
                        
            $rows = session("rows");            
            $rows['out_of_stock'] = $out_of_stock;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");            
            $rows['categories'] = session("tmp_categories");
            session(["rows" => $rows]);            
        }             
        else if($type == "sgammo_count")
        {
            if($crawler->filter('.pager-last.last a')->count() > 0)
            {
                $text = $crawler->filter('.pager-last.last a')->first()->attr("href");
                $text = explode("page=", trim($text));

                if(isset($text[1]))
                    return intval($text[1]);
                else
                    return 0;
            }

            return 0;
            
        }        
        else if($type == "sgammo")
        {
            if($crawler->filter(".views-view-grid tr")->count() >0 )
            {
                $crawler->filter(".views-view-grid tr")->each(function($tr){                    
                        if($tr->filter("td")->count() > 0)
                        {
                            $tr->filter("td")->each(function($td){
                                if($td->filter("a")->count() > 0)
                                {
                                    $rows = session("rows");
                                    $link = "https://www.sgammo.com".$td->filter("a")->first()->attr("href");
                                    if(!empty($link))
                                    {
                                        $rows[] = $link;    
                                        session(["rows" => $rows]);
                                    }                            
                                }
                            });
                        }
                });
            }            
        }        
        else if($type == "sgammo_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;
            
            if($crawler->filter("h1.title")->count() > 0)
            {
                $name = trim($crawler->filter("h1.title")->text());
            }
            
            if($crawler->filter("#content-body .product-body")->count() > 0)
            {
                $description = trim($crawler->filter("#content-body .product-body")->html());
            }
            
            if($crawler->filter("#product-details .uc-price-product")->count() > 0)
            {
                $old_price = trim($crawler->filter("#product-details .uc-price-product")->first()->text());
                $old_price = filterPrice($old_price);
            }
            
            session(["tmp_options" => []]);
            if($crawler->filter("#product-details .product-info.model")->count() > 0)
            {
                $tmp = trim($crawler->filter("#product-details .product-info.model")->first()->text());
                $tmp = str_replace('sku:', '', strtolower($tmp));
                $tmp = trim($tmp);
                if(!empty($tmp))
                {
                    $tmp_options = session("tmp_options");
                    $tmp_options[] = ["key" => "SKU", "value" => $tmp];
                    session(["tmp_options" => $tmp_options]);                    
                }
            }         
            
            session(["tmp_qty_options" => []]);
            if($crawler->filter(".quantity-table")->count() > 0)
            {
               $crawler->filter(".quantity-table tr")->each(function($tr){
                   if($tr->filter("td")->count() > 0)
                   {
                       if(trim($tr->filter("td")->first()->text()) == "QUANTITY PRICING")
                       {
                           // skip
                       }
                       else
                       {
                            $key = $tr->filter("td")->first()->text();
                            $value = $tr->filter("td")->last()->text();                            
                            if(!empty($key) && !empty($value) && $value != $key)
                            {
                                $tmp_options = session("tmp_qty_options");
                                $tmp_options[] = ["key" => $key, "value" => $value];
                                session(["tmp_qty_options" => $tmp_options]);                                                                               
                            }
                       }
                   }
                   
               }); 
            }
            
            session(["tmp_images" => []]);     

            if($crawler->filter("#product-thumb a")->count() > 0)
            {                
                $crawler->filter("#product-thumb a")->each(function ($row){                                        
                    $image = $row->attr("rel");                    
                    if(!empty($image))
                    {
                        $image = str_replace("/product_list/", "/product_full/", $image);
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }                    
                });
            }  
            else if($crawler->filter(".image .product-image a")->count() > 0)
            {
                $image = $crawler->filter(".image .product-image a")->attr("href");
                $image = str_replace("/product_list/", "/product_full/", $image);
                if(!empty($image))
                {
                    $tmp_images = session("tmp_images");
                    $tmp_images[] = ["image" => $image];
                    session(["tmp_images" => $tmp_images]);
                }                                    
            }

            session(["tmp_categories" => []]);
            if($crawler->filter(".breadcrumb a")->count() > 0)
            {
               $crawler->filter(".breadcrumb a")->each(function($li){
                   if(trim(strtolower($li->text())) != "home" && trim(strtolower($li->text())) != "catalog")
                   {
                       $categories = session("tmp_categories");
                       $categories[] = $li->text();
                       session(["tmp_categories" => $categories]);
                   }
               }); 
            }

         
            $rows = session("rows");            
            $rows['out_of_stock'] = 0;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");
            $rows['qty_options'] = session("tmp_qty_options");            
            $rows['categories'] = session("tmp_categories");
            session(["rows" => $rows]);

        }     
        else if($type == "righttobear_deals")
        {
            if($crawler->filter("#content_area .deal")->count() >0 )
            {
                $crawler->filter("#content_area .deal")->each(function($row){

                        if($row->filter(".deal-product-name a")->count() > 0)
                        {
                            $rows = session("rows");
                            $link = $row->filter(".deal-product-name a")->attr("href");
                            if(!empty($link))
                            {
                                $rows[] = $link;    
                                session(["rows" => $rows]);
                            }
                        }    
                });
            }
        }           
        else if($type == "righttobear_sales")
        {
            if($crawler->filter(".v-product-grid .v-product")->count() >0 )
            {
                $crawler->filter(".v-product-grid .v-product")->each(function($row){

                        if($row->filter("a")->count() > 0)
                        {
                            $rows = session("rows");
                            $link = $row->filter("a")->attr("href");
                            if(!empty($link))
                            {
                                $rows[] = $link;    
                                session(["rows" => $rows]);
                            }
                        }    
                });
            }
        }           
        else if($type == "righttobear_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;
            
            if($crawler->filter("#content_area span[itemprop='name']")->count() > 0)
            {
                $name = trim($crawler->filter("#content_area span[itemprop='name']")->text());
            }
            
            if($crawler->filter("#ProductDetail_ProductDetails_div #product_description")->count() > 0)
            {
                $description = trim($crawler->filter("#ProductDetail_ProductDetails_div #product_description")->html());
            }
            
            if($crawler->filter("#content_area .product_productprice span[itemprop='price']")->count() > 0)
            {
                $old_price = trim($crawler->filter("#content_area .product_productprice span[itemprop='price']")->first()->text());
                $old_price = filterPrice($old_price);
            }

            if($crawler->filter("#content_area .product_dealprice span[itemprop='price']")->count() > 0)
            {
                $special_price = trim($crawler->filter("#content_area .product_dealprice span[itemprop='price']")->first()->text());
                $special_price = filterPrice($special_price);
            }
            
            session(["tmp_options" => []]);
            if($crawler->filter("#content_area .product_code")->count() > 0)
            {
                $tmp = trim($crawler->filter("#content_area .product_code")->first()->text());
                if(!empty($tmp))
                {
                    $tmp_options = session("tmp_options");
                    $tmp_options[] = ["key" => "Product Code", "value" => $tmp];
                    session(["tmp_options" => $tmp_options]);                    
                }
            }         
            
            session(["tmp_images" => []]);     

            if($crawler->filter("#altviews a")->count() > 0)
            {                
                $crawler->filter("#altviews a")->each(function ($row){                                        
                    $image = $row->attr("href");                    
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }                    
                });
            }  
            else if($crawler->filter("#product_photo_zoom_url")->count() > 0)
            {
                $image = $crawler->filter("#product_photo_zoom_url")->first()->attr("href");
                if(!empty($image))
                {
                    $tmp_images = session("tmp_images");
                    $tmp_images[] = ["image" => $image];
                    session(["tmp_images" => $tmp_images]);
                }                
            }

            $out_of_stock = 0;
            if($crawler->filter("#content_area .outofstock")->count() > 0)
            {
                $out_of_stock = 1;
            }

            if($old_price == 0 && $special_price == 0)
            {
                if($crawler->filter(".colors_pricebox .product_productprice")->count() > 0)
                {
                    $tmp = trim($crawler->filter(".colors_pricebox .product_productprice")->text());
                    $tmp = str_replace("MSRP", "", $tmp);
                    $tmp = trim($tmp);
                    $old_price = filterPrice($tmp);
                }
                if($crawler->filter(".colors_pricebox .product_saleprice")->count() > 0)
                {
                    $tmp = trim($crawler->filter(".colors_pricebox .product_saleprice")->text());
                    $tmp = str_replace("SALE PRICE:", "", $tmp);
                    $tmp = trim($tmp);
                    $special_price = filterPrice($tmp);
                }
            }            
         
            $rows = session("rows");            
            $rows['out_of_stock'] = $out_of_stock;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");
            $rows['qty_options'] = session("tmp_qty_options");            
            $rows['categories'] = session("tmp_categories");
            session(["rows" => $rows]);
        }
        else if($type == "preppergunshop_count")
        {
            if($crawler->filter('.pager .amount')->count() > 0)
            {
                $text = $crawler->filter('.pager .amount')->first()->text();
                $text = explode("of", trim($text));

                if(isset($text[1]))
                {
                    $pages = str_replace("total", "", $text[1]);
                    $pages = intval($pages);
                    $perPage = 150;

                    if($pages > 0)
                    return ceil($pages/$perPage);

                }
                else
                    return 0;
            }

            return 0;
        }
        else if($type  == "preppergunshop")
        {
            if($crawler->filter("ul.products-grid")->count() >0 )
            {
                $crawler->filter("ul.products-grid")->each(function($row){
                    if($row->filter(".item")->count() > 0)
                    {       
                            $row->filter(".item")->each(function($li){
                                    if($li->filter("a")->count() > 0)
                                    {
                                        $rows = session("rows");
                                        $link = $li->filter("a")->first()->attr("href");
                                        if(!empty($link))
                                        {
                                            $rows[] = $link;    
                                            session(["rows" => $rows]);
                                        }
                                    }    
                            });
                    }    
                });
            }
        }
        else if($type  == "preppergunshop_detail")
        {
            $base_image = "";
            $name = "";
            $description = "";
            $reviewCount = 0;
            $stars = 0;
            $old_price = 0;
            $special_price = 0;
            $ext_date = null;
            
            if($crawler->filter(".product-name h1")->count() > 0)
            {
                $name = trim($crawler->filter(".product-name h1")->text());
            }
            
            if($crawler->filter(".box-collateral.box-description .box-collateral-content .std")->count() > 0)
            {
                $description = trim($crawler->filter(".box-collateral.box-description .box-collateral-content .std")->html());
            }
            
            if($crawler->filter(".product-view .old-price .price")->count() > 0)
            {
                $old_price = trim($crawler->filter(".product-view .old-price .price")->first()->text());
                $old_price = filterPrice($old_price);
            }
            else if($crawler->filter(".regular-price .price")->count() > 0)
            {
                $old_price = trim($crawler->filter(".regular-price .price")->first()->text());
                $old_price = filterPrice($old_price);
            }

            if($crawler->filter(".product-view .special-price .price")->count() > 0)
            {
                $special_price = trim($crawler->filter(".product-view .special-price .price")->first()->text());
                $special_price = filterPrice($special_price);
            }
            
            session(["tmp_options" => []]);
            if($crawler->filter("#product-attribute-specs-table tr")->count() > 0)
            {
                $crawler->filter("#product-attribute-specs-table tr")->each(function($tr){
                    if($tr->filter("th.label")->count() > 0 && $tr->filter("td.data")->count() > 0)
                    {
                         if($tr->filter("td.data .embed-responsive")->count() > 0 )
                         {
                            // do nothing
                         }   
                         else
                         {
                            $key = trim($tr->filter("th.label")->text());
                            $val = trim($tr->filter("td.data")->text());

                            if(!empty($key) && !empty($val))
                            {
                                $tmp_options = session("tmp_options");
                                $tmp_options[] = ["key" => $key, "value" => $val];
                                session(["tmp_options" => $tmp_options]);
                            }                                                    

                         }
                    }
                });
            }         
            
            session(["tmp_images" => []]);     

            if($crawler->filter(".my-gallery a.gallery-thumbnail")->count() > 0)
            {                
                $crawler->filter(".my-gallery a.gallery-thumbnail")->each(function ($row){                                        
                    $image = $row->attr("href");                    
                    if(!empty($image))
                    {
                        $tmp_images = session("tmp_images");
                        $tmp_images[] = ["image" => $image];
                        session(["tmp_images" => $tmp_images]);
                    }                    
                });
            }  

            $out_of_stock = 0;
            if($crawler->filter(".availability.out-of-stock")->count() > 0)
            {
                $out_of_stock = 1;
            }

         
            $rows = session("rows");            
            $rows['out_of_stock'] = $out_of_stock;
            $rows['image'] = "";
            $rows['name'] = $name;
            $rows['description'] = $description;
            $rows['special_price'] = $special_price;
            $rows['old_price'] = $old_price;
            $rows['ext_date'] = $ext_date;

            if($old_price >0 && $special_price > 0)
            {
                $rows['saving_price'] = $old_price - $special_price;
            }
            else
            {
                $rows['saving_price'] = 0;
            }
            
            $rows['images'] = session("tmp_images");
            $rows['specification'] = session("tmp_options");
            $rows['qty_options'] = session("tmp_qty_options");            
            $rows['categories'] = session("tmp_categories");
            session(["rows" => $rows]);
        }        
        return session("rows");
    }
}

?>