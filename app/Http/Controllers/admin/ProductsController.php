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
use App\Models\Product;

class ProductsController extends Controller {

    public function __construct() {

        $this->moduleRouteText = "products";
        $this->moduleViewName = "admin.products";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Products";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new Product();

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
        $data['sources'] = Product::groupBy("category")->pluck("category","category")->toArray();        
        $data['btnAdd'] = 0;
        return view($this->moduleViewName . ".index", $data);
    }

    public function pending() {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Pending Products";

        $data['add_url'] = route($this->moduleRouteText . '.create');
        $data['sources'] = Product::groupBy("category")->pluck("category","category")->toArray();        
        $data['btnAdd'] = 0;
        return view($this->moduleViewName . ".pending", $data);
    }
    
    public function show($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = Product::find($id);
        
        if(!$model)
            abort (404);
            

        $data = array();
        $data['page_title'] = "View Product";
        $data['deal'] = $model;
        return view($this->moduleViewName . ".show", $data);
    }

    public function edit($id) {
        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }
        
        $model = Product::find($id);
        
        if(!$model)
            abort (404);
            

        $data = array();
        $data['page_title'] = "Edit Product";
        $data['formObj'] = $model;
        $data['page_title'] = "Edit " . $this->module;
        $data['buttonText'] = "Update";
        $data['action_url'] = $this->moduleRouteText . ".update";
        $data['action_params'] = $model->id;
        $data['method'] = "PUT";

        return view($this->moduleViewName . ".edit", $data);
    }

    public function update(Request $request, $id)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_GALLERYGUNS);
        
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
            'title' => 'required|min:2',
            "product_id" => 'required|min:2|unique:products,product_id,'.$id
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
            $input = [];
            $input['product_id'] = $request->get("product_id");
            $input['title'] = $request->get("title");
            $input['description'] = $request->get("description");    
            $model->update($input); 

            //store logs detail
            $params=array();
            
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->EDIT_PRODUCT;
            $params['actionvalue']  = $id;
            $params['remark']       = "Edit Country::".$id;

            $logs=\App\Models\AdminLog::writeadminlog($params);           
        }
        
        return ['status' => $status,'msg' => $msg, 'data' => $data]; 

    }


    public function data(Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_GALLERYGUNS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = Product::query();         

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
                                        'isEdit' => \App\Models\Admin::isAccess(\App\Models\Admin::$EDIT_GALLERYGUNS),
                                        'isDelete' => 0,
                                        'isDeals' => 1,
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
                        ->rawColumns(['action'])
                        ->filter(function ($query) {                            

                            $search_gr_id = request()->get("search_gr_id");
                            $search_title = request()->get("search_title");
                            $category = request()->get("search_category");
                            $complete_product = request()->get("complete_product");

                            if($complete_product == 1)
                            {
                                $query = $query->whereRaw("(products.product_id IS NOT NULL AND trim(products.product_id) != '')");
                            }    

                            if($complete_product == 2)
                            {
                                $query = $query->whereRaw("(products.product_id IS NULL OR trim(products.product_id) = '')");
                            }    

                            if (!empty($search_title)) 
                            {
                                $query = $query->where(TBL_PRODUCTS.".title", 'LIKE', '%' . $search_title . '%');
                            }

                            if (!empty($category)) 
                            {
                                $query = $query->where(TBL_PRODUCTS.".category", 'LIKE', $category);
                            }   

                            if (!empty($search_gr_id)) 
                            {
                                $query = $query->where(TBL_PRODUCTS.".product_id", 'LIKE', "%".$search_gr_id."%");
                            }                            
                        })
                        ->make(true);
    }

}
