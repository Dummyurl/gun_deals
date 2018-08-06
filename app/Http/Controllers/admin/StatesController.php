<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Validator;
use Datatables;
use App\Models\State;
use App\Models\Country;
use App\models\AdminLog;
use App\Models\AdminAction;

class StatesController extends Controller
{
    public function __construct() {

        $this->moduleRouteText = "states";
        $this->moduleViewName = "admin.states";
        $this->list_url = route($this->moduleRouteText.".index");

        $module = "State";
        $this->module = $module;  

        $this->adminAction= new AdminAction; 
        
        $this->modelObj = new State();  

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
    public function index()
    {        
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $data = array();        
        $data['page_title'] = "Manage States";

        $data['add_url'] = route($this->moduleRouteText.'.create');
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_STATE);
                          
        $data['countryList'] = Country::pluck("title","id")->all();
        $data['state'] = State::pluck("title","id")->all(); 
         
        return view($this->moduleViewName.".index", $data);      
    }

    /**
     * Show the form for creating a new resource.   
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $data = array();
        $data['formObj'] = $this->modelObj;
        $data['page_title'] = "Add ".$this->module;
        $data['action_url'] = $this->moduleRouteText.".store";
        $data['action_params'] = 0;
        $data['buttonText'] = "Save";
        $data["method"] = "POST";
        $data['Country'] = Country::pluck("title","id")->all(); 
        
        return view($this->moduleViewName.'.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\m_responsekeys(conn, identifier)
     */
    public function store(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $status = 1;
        $msg = $this->addMsg;
        $data = array();

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'country_id' => 'required|exists:countries,id',
            
        ]);

        $title = $request->get('title');
        $country_id = $request->get('country_id');

        $query = \DB::table(TBL_STATE)
            ->where('title',$title)
            ->where('country_id',$country_id)
            ->first();

        if(!empty($query))
        {            
            $status = 0;
            $msg = "State already exists.";
            return ["status" => $status,'msg' => $msg];
        }
        
        // check validations
        if ($validator->fails())         
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
            $input = $request->all();
            $obj = $this->modelObj->create($input);
            $id = $obj->id;
            
            //store logs detail
            $params=array();    
                                    
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->ADD_STATE;
            $params['actionvalue']  = $id;
            $params['remark']       = "Add State::".$id;
                                    
            $logs=\App\Models\AdminLog::writeadminlog($params);

            session()->flash('success_message', $msg);           
            
        }
        
        return ['status' => $status, 'msg' => $msg, 'data' => $data];       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idate(format)
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }    

        $formObj = $this->modelObj->find($id);

        if(!$formObj)
        {
            abort(404);
        }   

        $data = array();
        $data['formObj'] = $formObj;
        $data['page_title'] = "Edit ".$this->module;
        $data['buttonText'] = "Update";
        $data['action_url'] = $this->moduleRouteText.".update";
        $data['action_params'] = $formObj->id;
        $data['method'] = "PUT";        

        $data['Country'] = Country::pluck("title","id")->all(); 
        return view($this->moduleViewName.'.add', $data);
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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $model = $this->modelObj->find($id);

        $status = 1;
        $msg = $this->updateMsg;
        $data = array();        
        
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'title' => 'required',
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
            $title = $request->get('title');
            $country_id = $request->get('country_id');
            
            $query = \DB::table(TBL_STATE)
                ->where('title',$title)
                ->where('country_id',$country_id)
                ->first();

            if($query && $query->id != $id)
            {            
                $status = 0;
                $msg = "State already exists.";
                return ["status" => $status,'msg' => $msg];
            }

            $input = $request->all();
            $model->update($input);                

           //store logs detail
            $params=array();
                                    
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->EDIT_STATE;
            $params['actionvalue']  = $id;
            $params['remark']       = "Edit State::".$id;
                                    
            $logs=\App\Models\AdminLog::writeadminlog($params);
            
            session()->flash('success_message', $msg);
        }
        
        return ['status' => $status, 'msg' => $msg, 'data' => $data];
               
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }     

        $modelObj = $this->modelObj->find($id);

        if($modelObj) 
        {
            try 
            {             
                $backUrl = $request->server('HTTP_REFERER');
                $modelObj->delete();
                session()->flash('success_message', $this->deleteMsg); 

                //store logs detail
                $params=array();
                
                $params['adminuserid']  = \Auth::guard('admins')->id();
                $params['actionid']     = $this->adminAction->DELETE_STATE;
                $params['actionvalue']  = $id;
                $params['remark']       = "Delete State::".$id;
                
                $logs=\App\Models\AdminLog::writeadminlog($params); 

                return redirect($backUrl);
            } 
            catch (Exception $e) 
            {
                session()->flash('error_message', $this->deleteErrorMsg);
                return redirect($this->list_url);
            }
        } 
        else 
        {
            session()->flash('error_message', "Record not exists");            
        }
    }
    public function data(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_STATE);
        
        if($checkrights) 
        {
            return $checkrights;
        }

        $model = State::select(TBL_STATE.".*",TBL_COUNTRY.".title as country" )
                ->join(TBL_COUNTRY,TBL_COUNTRY.".id","=",TBL_STATE.".country_id"); 

        return Datatables::eloquent($model)
                              
            ->addColumn('action', function(State $row) {
                return view("admin.states.action",
                    [
                        'currentRoute' => $this->moduleRouteText,
                        'row' => $row, 
                        'isEdit' => \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_STATE),
                        'isDelete' =>\App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_STATE),                                                       
                    ]
                )->render();
            })

            ->editColumn('created_at', function($row){
                
                if(!empty($row->created_at))                    
                    
            return date("j M, Y h:i:s A",strtotime($row->created_at));
                else
                    return '-';    
            })
            
            ->filter(function ($query) {
                
                $search_start_date = trim(request()->get("search_start_date"));                    
                $search_end_date = trim(request()->get("search_end_date"));
                $search_id = request()->get("search_id");                                         
                $search_state = request()->get("search_state");
                $search_country = request()->get("search_country");

                if (!empty($search_start_date)){

                    $from_date=$search_start_date.' 00:00:00';
                    $convertFromDate= $from_date;

                    $query = $query->where(TBL_STATE.".created_at",">=",addslashes($convertFromDate));
                }
                if (!empty($search_end_date)){

                    $to_date=$search_end_date.' 23:59:59';
                    $convertToDate= $to_date;

                    $query = $query->where(TBL_STATE.".created_at","<=",addslashes($convertToDate));
                }

                if(!empty($search_id))
                {
                    $idArr = explode(',', $search_id);
                    $idArr = array_filter($idArr);                
                    if(count($idArr)>0)
                    {
                        $query = $query->whereIn(TBL_STATE.".id",$idArr);
                    } 
                }                                         

                if(!empty($search_country))
                {
                    $query = $query->where(TBL_COUNTRY.".id", $search_country);
                }  
                
                if(!empty($search_state))
                {   
                    $query = $query->where(TBL_STATE.".title", 'LIKE', '%'.$search_state.'%');
                }                                                       
            })->make(true);        
    }        
        
}
