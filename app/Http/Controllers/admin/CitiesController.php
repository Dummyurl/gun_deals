<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Validator;
use Datatables;
use App\models\AdminAction;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {

        $this->moduleRouteText = "cities";
        $this->moduleViewName = "admin.cities";
        $this->list_url = route($this->moduleRouteText.".index");

        $module = "City";
        $this->module = $module;  
               
        $this->modelObj = new City();  
        $this->adminAction= new AdminAction;  

        $this->addMsg = $module . " has been added successfully!";
        $this->updateMsg = $module . " has been updated successfully!";
        $this->deleteMsg = $module . " has been deleted successfully!";
        $this->deleteErrorMsg = $module . " can not deleted!";
        
        view()->share("list_url", $this->list_url);
        view()->share("moduleRouteText", $this->moduleRouteText);
        view()->share("moduleViewName", $this->moduleViewName);

    }
    public function index()
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CITY);
        
        if($checkrights) 
        {
            return $checkrights;
        }
        $data = array();        
        $data['page_title'] = "Manage Cities"; 

        $data['add_url'] = route($this->moduleRouteText.'.create');
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_CITY);
         
        $data['city'] = City::pluck("title","id")->all();              
        $data['stateList'] = State::pluck("title","id")->all(); 
        $data['countryList'] = Country::pluck("title","id")->all(); 

        return view($this->moduleViewName.".index", $data);     
    }

    public function getstates(Request $request){
        
        
        $data = array();   

        $getstates = State::where('country_id', $request->country_id)->pluck("title","id");
        $options = "";
        
        if(!empty($getstates) && count($getstates) != 0){
            $options .= "<option value=''>select states</option>";
            foreach ($getstates as $key => $state) {
                    $options .= "<option value='$key'>$state</option>";
            }
        }else{
            $options .= "<option value=''>No States</option>";
        }
        echo $options;exit;       
        
    }

            

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_CITY);
        
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
        
        $data['stateList'] = State::pluck("title","id")->all(); 
        $data['countryList'] = Country::pluck("title","id")->all(); 
        
        return view($this->moduleViewName.'.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_CITY);
        
        if($checkrights) 
        {
            return $checkrights;
        }      
        $status = 1;
        $msg = $this->addMsg;
        $data = array();
        
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required',
            'title'     => 'required|min:2',
        ]);

        $title = $request->get('title');
        $state_id = $request->get('state_id');
        $country_id = $request->get('country_id');

        $query = \DB::table('cities as c')
            ->join('states as s','s.id','=','c.state_id')
            ->where('c.title',$title)
            ->where('c.state_id',$state_id)
            ->where('s.country_id',$country_id)
            ->first();
          
        if($query)
        {            
            $status = 0;
            $msg = "City already exists.";
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
            $title = $request->get('title');
            $state_id = $request->get('state_id');
           
            //$obj = $this->modelObj->create($input);
            $obj = $this->modelObj;
            $obj->title = $title;
            $obj->state_id = $state_id;
            $obj->save();
            $id = $obj->id;                       
            
            //store logs detail
            $params=array();    
                                    
            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->ADD_CITY ;
            $params['actionvalue']  = $id;
            $params['remark']       = "Add City::".$id;
                                    
            $logs=\App\Models\AdminLog::writeadminlog($params);

            session()->flash('success_message', $msg);
        }
        
        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_CITY);
        
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

        $data['stateList'] = State::pluck("title","id")->all(); 
        $data['countryList'] = Country::pluck("title","id")->all();
        
        $stateobj = \App\Models\State::find($formObj->state_id);
        if($stateobj){

            $data['def_state_id'] = $formObj->state_id;
            $data['def_c_id'] = $stateobj->country_id;

        }        
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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_CITY);
        
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
                'state_id' => 'required|exists:states,id',
                'title'     => 'required|min:2',
        ]);
        
        $title = $request->get('title');
        $state_id = $request->get('state_id');
        $country_id = $request->get('country_id');

        $query = \DB::table('cities as c')
            ->where('c.title',$title)
            ->where('c.state_id',$state_id)            
            ->where("c.id","!=",$id)    
            ->first();
          
        if($query)
        {            
            $status = 0;
            $msg = "City already exists.";
            return ["status" => $status,'msg' => $msg];
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
            //$input = $request->all();
            $title = $request->get('title');
            $state_id = $request->get('state_id');
            
            $model->title = $title;           
            $model->state_id = $state_id;           
            $model->save();           

            //store logs detail
                $params=array();
                
                $params['adminuserid']  = \Auth::guard('admins')->id();
                $params['actionid']     = $this->adminAction->EDIT_CITY;
                $params['actionvalue']  = $id;
                $params['remark']       = "Edit City::".$id;

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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_CITY);
        
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
                $params['actionid']     = $this->adminAction->DELETE_CITY;
                $params['actionvalue']  = $id;
                $params['remark']       = "Delete City::".$id;
                
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
            return redirect($this->list_url);
        }
    }

    public function data(Request $requesst)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CITY);
        
        if($checkrights) 
        {
            return $checkrights;
        }
        $model = City::select(TBL_CITY.".*",TBL_STATE.".title as state",TBL_COUNTRY.".title as country")
                ->join(TBL_STATE,TBL_STATE.".id","=",TBL_CITY.".state_id")
                ->join(TBL_COUNTRY,TBL_COUNTRY.".id","=",TBL_STATE.".country_id");
                
                return Datatables::eloquent($model)
               
            ->addColumn('action', function(City $row) {
                return view("admin.partials.action",
                    [
                        'currentRoute' => $this->moduleRouteText,
                        'row' => $row, 
                        'isEdit' => \App\Models\Admin::isAccess(\App\Models\Admin::$EDIT_CITY),
                        'isDelete' =>\App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_CITY),                                                        
                    ]
                )->render();
            })

            ->editColumn('created_at', function($row){
                
                if(!empty($row->created_at))         
                    return date("j M, Y h:i:s A",strtotime($row->created_at));
                else
                    return '-';    
            })
                
            ->filter(function ($query) 
            {
                $search_start_date = trim(request()->get("search_start_date"));                    
                $search_end_date = trim(request()->get("search_end_date"));
                $search_id = request()->get("search_id");
                $search_city = request()->get("search_city");                                       
                $search_state = request()->get("search_state");                                     
                $search_country = request()->get("search_country");               

                if (!empty($search_start_date)){

                    $from_date=$search_start_date.' 00:00:00';
                    $convertFromDate= $from_date;

                    $query = $query->where(TBL_CITY.".created_at",">=",addslashes($convertFromDate));
                }
                if (!empty($search_end_date)){

                    $to_date=$search_end_date.' 23:59:59';
                    $convertToDate= $to_date;

                    $query = $query->where(TBL_CITY.".created_at","<=",addslashes($convertToDate));
                }
                if(!empty($search_id))
                {
                    $idArr = explode(',', $search_id);
                    $idArr = array_filter($idArr);                
                    if(count($idArr)>0)
                    {
                        $query = $query->whereIn(TBL_CITY.".id",$idArr);
                    } 
                }                                

                if(!empty($search_city))
                {
                    $query = $query->where(TBL_CITY.".title", 'LIKE', '%'.$search_city.'%');
                }                                                       
                if(!empty($search_state))
                {
                    $query = $query->where(TBL_CITY.".state_id", $search_state);
                } 
                if(!empty($search_country))
                {
                    $query = $query->where(TBL_STATE.".country_id", $search_country);
                }  
                                                                   
            })->make(true);     
    }    
}
