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

    public static function deal_scraps($type, $pageURL)
    {
        session(["rows" => []]);

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

        if($type == "palmettostatearmory_count")
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

            if($crawler->filter('#price.prc p.prc')->count() > 0)
            {
                $special_price = trim($crawler->filter('#price.prc p.prc')->text());
                $special_price = str_replace("$", "", $special_price);
                $special_price = trim($special_price);
                $special_price = floatval($special_price);
            }
            else if($crawler->filter('#priceContainer .prc .sale')->count() > 0)
            {
                $special_price = trim($crawler->filter('#priceContainer .prc .sale')->text());
                $special_price = str_replace("$", "", $special_price);
                $special_price = trim($special_price);
                $special_price = floatval($special_price);
            }

            if($crawler->filter('#priceContainer .prc .strike')->count() > 0)
            {
                $old_price = trim($crawler->filter('#priceContainer .prc .strike')->text());
                $old_price = str_replace("$", "", $old_price);
                $old_price = trim($old_price);
                $old_price = floatval($old_price);
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
                        if(isset($jsonData[$key]['LoadSKUSpecificInfo']['PriceText']))
                        {
                            $html = $jsonData[$key]['LoadSKUSpecificInfo']['PriceText'];
                            preg_match_all("/<span class='strike'>(.*?)<\/span>/s", $html, $matches);


                            if(isset($matches[1][0]))
                            {
                                $old_price = trim($matches[1][0]);
                                $old_price = str_replace("$", "", $old_price);
                                $old_price = trim($old_price);
                                $old_price = floatval($old_price);
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

            if($old_price == 0)
            {
                if($crawler->filter('#priceContainer .prc p')->count() > 0)
                {
                    $old_price = trim($crawler->filter('priceContainer .prc p')->text());
                    $old_price = str_replace("$", "", $old_price);
                    $old_price = trim($old_price);
                    $old_price = floatval($old_price);
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
            session(["rows" => $rows]);            
        }             

        return session("rows");
    }
}

?>