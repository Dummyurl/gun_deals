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
use App\Models\GallaryGuns;

class GalleryGunsController extends Controller {

    public function __construct() {

        $this->moduleRouteText = "galleryofguns";
        $this->moduleViewName = "admin.galleryofguns";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Gallary Of Guns";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new GallaryGuns();

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
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Products";

        $data['add_url'] = route($this->moduleRouteText . '.create');
        $data['sources'] = GallaryGuns::groupBy("category")->pluck("category","category")->toArray();        
        $data['btnAdd'] = 0;
        return view($this->moduleViewName . ".index", $data);
    }
    
    public function show($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = GallaryGuns::find($id);
        
        if(!$model)
            abort (404);
            

        $data = array();
        $data['page_title'] = "View Product";
        $data['deal'] = $model;
        return view($this->moduleViewName . ".show", $data);
    }

    public function data(Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = GallaryGuns::query();         

        return Datatables::eloquent($model)
                        ->addColumn('action', function($row) {
                            
                            $isView = 1;
                            if($row->category != 'Revolver All Types')
                            {
                                //$isView = 0;
                            }    
                            
                            return view($this->moduleViewName . ".action", [
                                        'currentRoute' => $this->moduleRouteText,
                                        'row' => $row,
                                        'isEdit' => 0,
                                        'isDelete' => 0,
                                        'isView' => $isView,
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
                            $category = request()->get("search_category");

                            if (!empty($search_title)) 
                            {
                                $query = $query->where(TBL_GALLERYGUNS.".title", 'LIKE', '%' . $search_title . '%');
                            }

                            if (!empty($category)) 
                            {
                                $query = $query->where(TBL_GALLERYGUNS.".category", 'LIKE', $category);
                            }                            
                        })
                        ->make(true);
    }

}
