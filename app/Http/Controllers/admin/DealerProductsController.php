<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Datatables;
use Validator;
use App\Models\AdminUser;
use App\Models\AdminAction;
use App\Models\AdminUserType;
use App\Models\ScrapSource;
use App\Models\DealerProduct;

class DealerProductsController extends Controller
{
    public function __construct() {

        $this->moduleRouteText = "dealer-products";
        $this->moduleViewName = "admin.dealer_products";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Dealer Product";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new DealerProduct();

        $this->addMsg = $module . " has been added successfully!";
        $this->updateMsg = $module . " has been updated successfully!";
        $this->deleteMsg = $module . " has been deleted successfully!";
        $this->deleteErrorMsg = $module . " can not deleted!";

        view()->share("list_url", $this->list_url);
        view()->share("moduleRouteText", $this->moduleRouteText);
        view()->share("moduleViewName", $this->moduleViewName);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALER_PRODUCTS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Dealer Products";
        $data['sources'] = ScrapSource::pluck('title','id')->all();

        return view($this->moduleViewName . ".index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALER_PRODUCTS);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = DealerProduct::find($id);
        
        if(!$model)
            abort (404);

        $data = array();
        $data['page_title'] = "View Dealer Product";
        $data['dealerProduct'] = DealerProduct::select(TBL_DEALER_PRODUCTS.'.*',TBL_DEAL_SOURCE.'.title as source_title')
                     ->join(TBL_DEAL_SOURCE,TBL_DEAL_SOURCE.'.id','=',TBL_DEALER_PRODUCTS.'.source_id')
                     ->where(TBL_DEALER_PRODUCTS.'.id',$id)
                     ->first();
        return view($this->moduleViewName . ".show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function data(Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALER_PRODUCTS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = DealerProduct::select(TBL_DEALER_PRODUCTS.'.*',TBL_DEAL_SOURCE.'.title as source_title')->join(TBL_DEAL_SOURCE,TBL_DEAL_SOURCE.'.id','=',TBL_DEALER_PRODUCTS.'.source_id');

        return Datatables::eloquent($model)
            ->addColumn('action', function($row) {
                return view("admin.dealer_products.action", 
                          [
                            'currentRoute' => $this->moduleRouteText,
                            'row' => $row,
                            'isView' => \App\Models\Admin::isAccess(\App\Models\Admin::$LIST_DEALER_PRODUCTS),
                           ]
                        )->render();
            })
            ->editColumn('created_at', function($row){
                if(!empty($row->created_at))                    
                    return date("j M, Y",strtotime($row->created_at));
                else
                    return '-';    
            })
            
            ->rawColumns(['action'])
            ->filter(function ($query) {
                $search_gr_id = request()->get("search_gr_id");
                $search_title = request()->get("search_title");
                $search_source = request()->get("search_source");
                $complete_product = request()->get("complete_product");

                if($complete_product == 1)
                {
                    $query = $query->whereRaw("(dealer_products.product_id IS NOT NULL AND trim(dealer_products.product_id) != '')");
                }    

                if($complete_product == 2)
                {
                    $query = $query->whereRaw("(dealer_products.product_id IS NULL OR trim(dealer_products.product_id) = '')");
                }
                if (!empty($search_gr_id)) {
                    $query = $query->where(TBL_DEALER_PRODUCTS.".product_id", 'LIKE', '%' . $search_gr_id . '%');
                }
                if (!empty($search_title)) {
                    $query = $query->where(TBL_DEALER_PRODUCTS.".title", 'LIKE', '%' . $search_title . '%');
                }
                if (!empty($search_source)) {
                    $query = $query->where(TBL_DEAL_SOURCE.".id", $search_source);
                }
            })
            ->make(true);
    }

    public function pending() {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALER_PRODUCTS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Pending Dealer Products";
        $data['sources'] = ScrapSource::pluck('title','id')->all();

        return view($this->moduleViewName . ".pending", $data);
    }
}
