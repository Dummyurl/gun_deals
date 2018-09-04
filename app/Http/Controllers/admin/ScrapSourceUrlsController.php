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
use App\Models\ScrapSourceUrl;
use App\Models\ProductCategory;

class ScrapSourceUrlsController extends Controller {

    public function __construct() {

        $this->moduleRouteText = "scrap-source-urls";
        $this->moduleViewName = "admin.scrap-source-urls";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Scrap source url";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new ScrapSourceUrl();

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
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_SCRAP_URLS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Scrap Source Urls";

        $data['add_url'] = route($this->moduleRouteText . '.create');
        $data['sources'] = ScrapSource::pluck("title","id")->toArray();        
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_SCRAP_URL);
        return view($this->moduleViewName . ".index", $data);
    }

    public function create() {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_SCRAP_URL);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['formObj'] = $this->modelObj;
        $data['page_title'] = "Add " . $this->module;
        $data['action_url'] = $this->moduleRouteText . ".store";
        $data['action_params'] = 0;
        $data['buttonText'] = "Save";
        $data["method"] = "POST";
        $data['sources'] = ScrapSource::pluck("title","id")->toArray();
        $data['categories'] = ProductCategory::pluck("title","id")->toArray();        
        return view($this->moduleViewName . '.add', $data);
    }

    public function store(Request $request) {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_SCRAP_URL);

        if ($checkrights) 
        {
            return $checkrights;
        }

        $status = 1;
        $msg = $this->addMsg;
        $data = array();

        $validator = Validator::make($request->all(), [
            'source_id' => 'required',
            'source_type' => 'required',
            'scrap_url' => 'required|url',
            'status' => 'required',
        ]);

        // check validations
        if ($validator->fails()) {
            $messages = $validator->messages();

            $status = 0;
            $msg = "";

            foreach ($messages->all() as $message) {
                $msg .= $message . "<br />";
            }
        } 
        else 
        {
            $input = $request->all();

            $category_id = $request->get("category_id");
            if(empty($category_id))
                $category_id = null;

            $model = new ScrapSourceUrl();
            $model->source_id = $request->get("source_id");
            $model->source_type = $request->get("source_type");
            $model->scrap_url = $request->get("scrap_url");
            $model->status = $request->get("status");
            $model->category_id = $category_id;
            $model->save();

            $id = $model->id;

            // store logs detail
            $params = array();
            $params['adminuserid'] = \Auth::guard('admins')->id();
            $params['actionid'] = $this->adminAction->ADD_SCRAP_URL;
            $params['actionvalue'] = $id;
            $params['remark'] = "Add ::" . $id;
            $logs = \App\Models\AdminLog::writeadminlog($params);
            session()->flash('success_message', $msg);
        }

        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }
    

    public function edit($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_SCRAP_URL);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = ScrapSourceUrl::find($id);
        
        if(!$model)
            abort (404);
            

        $data = array();
        $data['formObj'] = $model;
        $data['page_title'] = "Edit " . $this->module;
        $data['buttonText'] = "Update";
        $data['action_url'] = $this->moduleRouteText . ".update";
        $data['action_params'] = $model->id;
        $data['method'] = "PUT";
        $data['sources'] = ScrapSource::pluck("title","id")->toArray();
        $data['categories'] = ProductCategory::pluck("title","id")->toArray();                
        return view($this->moduleViewName . ".add", $data);
    }

    public function update(Request $request, $id)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_SCRAP_URL);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $model = $this->modelObj->find($id);

        $status = 1;
        $msg = $this->updateMsg;
        $data = array();        
        
        $validator = Validator::make($request->all(), 
        [
            'source_id' => 'required',
            'source_type' => 'required',
            'scrap_url' => 'required|url',
            'status' => 'required',            
        ]);
        
        // check validations
        if(!$model)
        {
            $status = 0;
            $msg = "Record not found !";
        }
        else if ($validator->fails()) 
        {
            $messages = $validator->messages();
            
            $status = 0;
            $msg = "";
            
            foreach ($messages->all() as $message) 
            {
                $msg .= $message . "<br />";
            }
        }         
        else
        {            
            $category_id = $request->get("category_id");
            if(empty($category_id))
                $category_id = null;

            $model->source_id = $request->get("source_id");
            $model->source_type = $request->get("source_type");
            $model->scrap_url = $request->get("scrap_url");
            $model->status = $request->get("status");
            $model->category_id = $category_id;            
            $model->save();


            //store logs detail
            $params=array();
            
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->EDIT_SCRAP_URL;
            $params['actionvalue']  = $id;
            $params['remark']       = "Edit ::".$id;

            $logs=\App\Models\AdminLog::writeadminlog($params);           
        }
        
        return ['status' => $status,'msg' => $msg, 'data' => $data]; 

    }

    public function destroy($id, Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_SCRAP_URL);

        if ($checkrights) {
            return $checkrights;
        }

        $modelObj = $this->modelObj->find($id);

        if ($modelObj) {
            try {
                $backUrl = $request->server('HTTP_REFERER');
                $modelObj->delete();
                session()->flash('success_message', $this->deleteMsg);

                //store logs detail
                $params = array();

                $params['adminuserid'] = \Auth::guard('admins')->id();
                $params['actionid'] = $this->adminAction->DELETE_SCRAP_URL;
                $params['actionvalue'] = $id;
                $params['remark'] = "Delete ::" . $id;

                $logs = \App\Models\AdminLog::writeadminlog($params);

                return redirect($backUrl);
            } catch (Exception $e) {
                session()->flash('error_message', $this->deleteErrorMsg);
                return redirect($this->list_url);
            }
        } else {
            session()->flash('error_message', "Record not exists");
            return redirect($this->list_url);
        }
    }

    public function data(Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_SCRAP_URLS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = ScrapSourceUrl::join(TBL_DEAL_SOURCE,TBL_DEAL_SOURCE.".id","=",TBL_SCRAP_URLS.".source_id")
                ->select(TBL_SCRAP_URLS.".*",TBL_DEAL_SOURCE.".title as source_title");         

        return Datatables::eloquent($model)
                        ->addColumn('action', function($row) {
                            
                            $isView = 1;
                            
                            return view($this->moduleViewName . ".action", [
                                        'currentRoute' => $this->moduleRouteText,
                                        'row' => $row,
                                        'isEdit' => \App\Models\Admin::isAccess(\App\Models\Admin::$EDIT_SCRAP_URL),
                                        'isDelete' => \App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_SCRAP_URL),
                                        'isView' => $isView,
                                            ]
                                    )->render();
                        })
                        ->editColumn('created_at', function($row){
                            if(!empty($row->created_at))                    
                                return date("j M, Y",strtotime($row->created_at));
                            else
                                return '-';    
                        })                        
                        ->editColumn('status', function($row){
                            if ($row->status == 1)
                                return '<span class="label label-success">Active</span>';
                            else
                                return '<span class="label label-danger">Inactive</span>';                            
                        })                        
                        ->rawColumns(['action','status'])
                        ->filter(function ($query) {     
                            $category = request()->get("search_category");
                            if(!empty($category)) 
                            {
                                $query = $query->where(TBL_SCRAP_URLS.".category_id", 'LIKE', $category);
                            }   
                        })
                        ->make(true);
    }
}
