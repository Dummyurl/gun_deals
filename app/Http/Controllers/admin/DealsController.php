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

        $model = Deal::select(TBL_DEALS.".*",TBL_DEAL_SOURCE.".title as source_title" )
                ->join(TBL_DEAL_SOURCE,TBL_DEAL_SOURCE.".id","=",TBL_DEALS.".source_id");         

        return Datatables::eloquent($model)
                        ->addColumn('action', function($row) {
                            return view($this->moduleViewName . ".action", [
                                        'currentRoute' => $this->moduleRouteText,
                                        'row' => $row,
                                        'isEdit' => 0,
                                        'isDelete' => 0,
                                        'isView' => 1,
                                            ]
                                    )->render();
                        })
                        ->editColumn('created_at', function($row){
                            if(!empty($row->created_at))                    
                                return date("j M, Y h:i:s A",strtotime($row->created_at));
                            else
                                return '-';    
                        })                        
                        ->rawColumns(['action'])
                        ->filter(function ($query) {                            
                            $search_title = request()->get("search_title");
                            $source_id = request()->get("source_id");
                            if (!empty($search_title)) {
                                $query = $query->where(TBL_DEALS.".title", 'LIKE', '%' . $search_title . '%');
                            }
                            if (!empty($source_id)) {
                                $query = $query->where(TBL_DEALS.".source_id", $source_id);
                            }
                        })
                        ->make(true);
    }

}
