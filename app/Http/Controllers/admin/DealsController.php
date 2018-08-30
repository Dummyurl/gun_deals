<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Datatables;
use Validator;
use App\Models\AdminUser;
use App\Models\AdminAction;
use App\Models\AdminUserType;
use App\Models\ScrapSource;
use App\Models\Deal;

class DealsController extends Controller {

    public function __construct() {

        $this->moduleRouteText = "deals";
        $this->moduleViewName = "admin.deals";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Deal";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new Deal();

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
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALS);

        if ($checkrights) {
            return $checkrights;
        }

        if(request()->get("featured_action") == 2 || request()->get("featured_action") == 1 && request()->get("id") > 0)
        {
            $id = request()->get("id");

            $deal = Deal::find($id);

            if($deal)
            {
                $msg = "";

                if(request()->get("featured_action") == 1)
                {
                    $deal->is_featured = 1;                                        
                    $msg = "Deal has been updated as featured.";
                }    
                else
                {
                    $msg = "Deal has been removed as featured.";
                    $deal->is_featured = 0;                                                            
                }

                $deal->save();
                session()->flash('success_message', $msg);
            }
            else
            {
                $msg = "Deal not found !";
                session()->flash('error_message', $msg);
            }

            return redirect($this->list_url);
        }

        $data = array();
        $data['page_title'] = "Manage Deals";

        $data['add_url'] = route($this->moduleRouteText . '.create');
        $data['sources'] = ScrapSource::pluck("title","id")->toArray();        
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_DEAL);
        return view($this->moduleViewName . ".index", $data);
    }
    
    public function show($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALS);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = Deal::find($id);
        
        if(!$model)
            abort (404);
            

        $data = array();
        $data['page_title'] = "View Deal";
        $data['deal'] = $model;
        return view($this->moduleViewName . ".show", $data);
    }

    public function data(Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_DEALS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = Deal::select(TBL_DEALS.".*",TBL_DEAL_SOURCE.".title as source_title",TBL_PRODUCTS.".title as product_title")
                ->join(TBL_DEAL_SOURCE,TBL_DEAL_SOURCE.".id","=",TBL_DEALS.".source_id")
                ->leftJoin(TBL_PRODUCTS, TBL_PRODUCTS.".id", "=", TBL_DEALS.".product_id");         

        return Datatables::eloquent($model)
                        ->addColumn('action', function($row) {
                            return view($this->moduleViewName . ".action", [
                                        'currentRoute' => $this->moduleRouteText,
                                        'row' => $row,
                                        'isEdit' => 0,
                                        'isDelete' => 0,
                                        'isChangeStatus' => 1,
                                        'isView' => 1,
                                            ]
                                    )->render();
                        })
                        ->editColumn('is_featured', function($row) {

                            $html = '';

                            if($row->is_featured == 1)
                            {
                                $html = '<center><span class="label label-success">Yes</span>';
                            }
                            else
                            {
                                $html = '<center><span class="label label-danger">No</span></center>';
                            }

                            return $html;
                        })    
                        ->editColumn('product_title', function($row) {

                            $html = '';

                            if($row->product_id > 0)
                            {
                                $html = '<a target="_blank" title="View Product" href="'.url('admin/products/'.$row->product_id).'">'.$row->product_title.'</a>';
                            }

                            return $html;
                        })    
                        ->editColumn('created_at', function($row){
                            if(!empty($row->created_at))                    
                                return date("j M, Y h:i:s A",strtotime($row->created_at));
                            else
                                return '-';    
                        })                        
                        ->rawColumns(['action','is_featured','product_title'])
                        ->filter(function ($query) {                            
                            $search_title = request()->get("search_title");
                            $source_id = request()->get("source_id");
                            $search_product_id = request()->get("search_product_id");
                            $search_featured = request()->get("search_featured");
                            $onlymap_deals = request()->get("onlymap_deals");
                            
                            if (!empty($search_title)) {
                                $query = $query->where(TBL_DEALS.".title", 'LIKE', '%' . $search_title . '%');
                            }

                            if($search_featured == 1)
                            {
                                $query = $query->where(TBL_DEALS.".is_featured", 1);
                            }
                            else if($search_featured == 2)
                            {
                                $query = $query->where(TBL_DEALS.".is_featured", 0);
                            }                            

                            if($search_product_id > 0)
                            {
                                $query = $query->where(TBL_DEALS.".product_id", $search_product_id);
                            }

                            if (!empty($source_id)) 
                            {
                                $query = $query->where(TBL_DEALS.".source_id", $source_id);
                            }

                            if($onlymap_deals == 1)
                            {
                                $query = $query->where(TBL_DEALS.".product_id", ">",0);
                            }
                        })
                        ->make(true);
    }

}
