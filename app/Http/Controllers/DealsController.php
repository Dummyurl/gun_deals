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
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Deal;

class DealsController extends Controller
{
    public function __construct()
    {
    }

    public function listing($category,$sub_category = '',$third_sub_category = '')
    {
        $current_url = request()->path();
        $data = [];

        $category = ProductCategory::where("url",$current_url)->first();
        $data['page_title'] = $category->title;

        if(!$category)
            abort(404);

        $id = $category->id;

        $breadCrumbsLinks = explode("/", $current_url);
        $breadcrums = [];

        $i = 0;
        foreach($breadCrumbsLinks as $url)
        {            

            $customURL = "";

            for($j = 0; $j<=$i;$j++)
            {                    
                $customURL .= $breadCrumbsLinks[$j]."/";
            }

            $customURL = rtrim($customURL,"/");

            $category = ProductCategory::where("url",$customURL)->first();

            if($category)
            {
                $breadcrums[] = 
                [
                    "title" => $category->title,
                    "link" => url($category->url),
                ];
            }

            $i++;
        }

        $data['breadcrums'] = $breadcrums;

        $ids = getChildrens($id);
        $ids[] = $id;

        $data['rows'] = \App\Models\Product::join("deals","deals.product_id","=","products.id")
                        ->select("deals.*")    
                        ->whereIn("products.product_category_id",$ids)
                        ->paginate(10);

        return view("listing", $data);
    }    
}
