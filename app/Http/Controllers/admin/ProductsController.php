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
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_GALLERYGUNS);
        return view($this->moduleViewName . ".index", $data);
    }

    public function create() {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_GALLERYGUNS);

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

        return view($this->moduleViewName . '.add', $data);
    }

    public function store(Request $request) {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_GALLERYGUNS);

        if ($checkrights) 
        {
            return $checkrights;
        }

        $status = 1;
        $msg = $this->addMsg;
        $data = array();

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2',
            "product_id" => 'required|min:2|unique:products,product_id',
            "upc_number" => 'required|min:2|unique:products,upc_number',            
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
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

            $model = new Product();
            $model->product_id = $request->get("product_id");
            $model->item_unique_id = $request->get("item_unique_id");
            $model->item_id = $request->get("item_unique_id");
            $model->title = $request->get("title");
            $model->description = $request->get("description");
            $model->upc_number = $request->get("upc_number");
            $model->msrp = $request->get("msrp");
            $model->brand = $request->get("brand");
            $model->model = $request->get("model");
            $model->item_id = NULL;
            $model->link = NULL;
            $model->link_md5 = md5($request->get("item_unique_id"));
            $model->category = "custom";
            $model->save();

            $id = $model->id;

            $keys = $request->get("key");
            $values = $request->get("value");

            $dataToInsert = [];

            if(is_array($keys) && count($keys) > 0)
            {
                foreach($keys as $k => $v)
                {
                    $key = $keys[$k];
                    $value = isset($values[$k]) ? $values[$k]:"";

                    if(!empty($key) && !empty($value))
                    {
                        $dataToInsert[] = 
                        [
                            "product_id" => $id,
                            "keyname" => $key,
                            "keyvalue" => $value
                        ];
                    }
                }

                if(count($dataToInsert))
                {
                    \DB::table("product_attributes")
                    ->insert($dataToInsert);
                }
            }

            $UPLOAD_DIR_PATH = env("UPLOAD_DIR_PATH");
            if($request->hasFile('image'))
            {
                $imageName = time() . '.' . $request->image->getClientOriginalExtension();
                $uploadPath = 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $id;
                $request->image->move($uploadPath, $imageName);
                $model->image = url("uploads/products/$id/".$imageName);
                $model->save();
            }

            // store logs detail
            $params = array();
            $params['adminuserid'] = \Auth::guard('admins')->id();
            $params['actionid'] = $this->adminAction->ADD_PRODUCT;
            $params['actionvalue'] = $id;
            $params['remark'] = "Add ::" . $id;
            $logs = \App\Models\AdminLog::writeadminlog($params);
            session()->flash('success_message', $msg);
        }

        return ['status' => $status, 'msg' => $msg, 'data' => $data];
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
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_GALLERYGUNS);
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

        return view($this->moduleViewName . ".add", $data);
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
            "product_id" => 'required|min:2|unique:products,product_id,'.$id,            
            "upc_number" => 'required|min:2|unique:products,upc_number,'.$id,            
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048'
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
            
            $model->product_id = $request->get("product_id");
            $model->item_unique_id = $request->get("item_unique_id");
            $model->item_id = $request->get("item_unique_id");

            $model->title = $request->get("title");
            $model->description = $request->get("description");
            $model->upc_number = $request->get("upc_number");
            $model->msrp = $request->get("msrp");
            $model->brand = $request->get("brand");
            $model->model = $request->get("model");
            $model->item_id = NULL;
            $model->link = NULL;
            $model->link_md5 = md5($request->get("item_unique_id"));
            $model->category = "custom";
            $model->save();


            $keys = $request->get("key");
            $values = $request->get("value");

            $dataToInsert = [];

            \DB::table("product_attributes")
            ->where("product_id",$id)
            ->delete();

            if(is_array($keys) && count($keys) > 0)
            {
                foreach($keys as $k => $v)
                {
                    $key = $keys[$k];
                    $value = isset($values[$k]) ? $values[$k]:"";

                    if(!empty($key) && !empty($value))
                    {
                        $dataToInsert[] = 
                        [
                            "product_id" => $id,
                            "keyname" => $key,
                            "keyvalue" => $value
                        ];
                    }
                }

                if(count($dataToInsert))
                {
                    \DB::table("product_attributes")
                    ->insert($dataToInsert);
                }
            }

            $UPLOAD_DIR_PATH = env("UPLOAD_DIR_PATH");
            if($request->hasFile('image'))
            {
                $imageName = time() . '.' . $request->image->getClientOriginalExtension();
                $uploadPath = 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $id;
                $request->image->move($uploadPath, $imageName);
                $model->image = url("uploads/products/$id/".$imageName);
                $model->save();
            }


            //store logs detail
            $params=array();
            
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->EDIT_PRODUCT;
            $params['actionvalue']  = $id;
            $params['remark']       = "Edit ::".$id;

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
                                        'isDelete' => \App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_GALLERYGUNS),
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

    public function destroy($id, Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_GALLERYGUNS);

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
                $params['actionid'] = $this->adminAction->DELETE_PRODUCT;
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

}
