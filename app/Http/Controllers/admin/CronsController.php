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
use App\Models\CronLog;
use App\Models\CronLogDetail;

class CronsController extends Controller {

    public function __construct() {

        $this->moduleRouteText = "crons";
        $this->moduleViewName = "admin.crons";
        $this->list_url = route($this->moduleRouteText . ".index");

        $module = "Cron";
        $this->module = $module;

        $this->adminAction = new AdminAction;
        $this->modelObj = new CronLog();

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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CRONS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage Crons";

        $data['add_url'] = route($this->moduleRouteText . '.create');
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_CRON);

        return view($this->moduleViewName . ".index", $data);
    }

    public function Log() {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CRONS);

        if ($checkrights) {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "List Cron Logs";     

        $data['search_name'] = CronLog::pluck("cron_name",'id')->all();

        $this->list_url = url("admin/cron-log");
        view()->share("list_url", $this->list_url);   
        return view($this->moduleViewName . ".log", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_CRON);

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_CRON);

        if ($checkrights) {
            return $checkrights;
        }

        $status = 1;
        $msg = $this->addMsg;
        $data = array();

        $validator = Validator::make($request->all(), [
            'cron_name' => 'required|min:2',
            'cron_url' => 'required|min:2',
            'cron_interval' => 'required|min:2',            
        ]);

        // check validations
        if ($validator->fails()) {
            $messages = $validator->messages();

            $status = 0;
            $msg = "";

            foreach ($messages->all() as $message) {
                $msg .= $message . "<br />";
            }
        } else {
            $input = $request->all();
            $obj = $this->modelObj->create($input);
            $id = $obj->id;
            //store logs detail
            $params = array();

            $params['adminuserid'] = \Auth::guard('admins')->id();
            $params['actionid'] = $this->adminAction->ADD_CRON;
            $params['actionvalue'] = $id;
            $params['remark'] = "Add ::" . $id;

            $logs = \App\Models\AdminLog::writeadminlog($params);

            session()->flash('success_message', $msg);
        }

        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_CRON);

        if ($checkrights) {
            return $checkrights;
        }

        $formObj = $this->modelObj->find($id);

        if (!$formObj) {
            abort(404);
        }

        $data = array();
        $data['formObj'] = $formObj;
        $data['page_title'] = "Edit " . $this->module;
        $data['buttonText'] = "Update";

        $data['action_url'] = $this->moduleRouteText . ".update";
        $data['action_params'] = $formObj->id;
        $data['method'] = "PUT";

        return view($this->moduleViewName . '.add', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_CRON);

        if ($checkrights) {
            return $checkrights;
        }

        $model = $this->modelObj->find($id);

        $status = 1;
        $msg = $this->updateMsg;
        $data = array();

        $validator = Validator::make($request->all(),[
            'cron_name' => 'required|min:2',
            'cron_url' => 'required|min:2',
            'cron_interval' => 'required|min:2',            
        ]);

        // check validations
        if (!$model) {
            $status = 0;
            $msg = "Record not found !";
        } else if ($validator->fails()) {
            $messages = $validator->messages();

            $status = 0;
            $msg = "";

            foreach ($messages->all() as $message) {
                $msg .= $message . "<br />";
            }
        } else {
            $input = $request->all();
            $model->update($input);

            //store logs detail
            $params = array();

            $params['adminuserid'] = \Auth::guard('admins')->id();
            $params['actionid'] = $this->adminAction->EDIT_CRON;
            $params['actionvalue'] = $id;
            $params['remark'] = "Edit ::" . $id;

            $logs = \App\Models\AdminLog::writeadminlog($params);
        }

        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_CRON);

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
                $params['actionid'] = $this->adminAction->DELETE_CRON;
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
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CRONS);

        if ($checkrights) {
            return $checkrights;
        }

        $model = CronLog::query();

        return Datatables::eloquent($model)
                        ->addColumn('action', function($row) {
                            return view($this->moduleViewName.".action", 
                                      [
                                        'currentRoute' => $this->moduleRouteText,
                                        'row' => $row,
                                        'isEdit' => \App\Models\Admin::isAccess(\App\Models\Admin::$EDIT_CRON),
                                        'isDelete' => \App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_CRON),
                                       ]
                                    )->render();
                        })
                        ->editColumn('created_at', function($row){                
                            if(!empty($row->created_at))
                                return date("j M, Y H:i:s A",strtotime($row->created_at));
                            else
                                return '-';    
                        })                 
                        ->filter(function ($query) 
                        {
                            $search_start_date = trim(request()->get("search_start_date")); 
                            $search_end_date = trim(request()->get("search_end_date")); 
                            $search_id = request()->get("search_id");                                       
                            $search_name = request()->get("search_name");  
                            $search_url = request()->get("search_url");    
                            $search_interval = request()->get("search_interval");                                         
                            if (!empty($search_start_date)) 
                            {
                                $from_date = $search_start_date.' 00:00:00';
                                $convertFromDate = $from_date;
                                 
                                $query = $query->where("cron_log.created_at",">=",addslashes($convertFromDate));
                            }
                
                            if (!empty($search_end_date)) 
                            {
                                $to_date = $search_end_date.' 23:59:59';
                                $convertToDate = $to_date;
                                
                                $query = $query->where("cron_log.created_at","<=",addslashes($convertToDate));
                            }
                            if(!empty($search_id))
                            {
                                $idArr = explode(',', $search_id);
                                $idArr = array_filter($idArr);                
                                if(count($idArr)>0)
                                {
                                    $query = $query->whereIn('cron_log.id',$idArr);
                                } 
                            }
                            if(!empty($search_name))
                            {
                                $query = $query->where('cron_log.cron_name', 'LIKE', '%'.$search_name.'%');
                            } 
                            if(!empty($search_url))
                            {
                                $query = $query->where('cron_log.cron_url', 'LIKE', '%'.$search_url.'%');
                            } 
                            if(!empty($search_interval))
                            {
                                $query = $query->where('cron_log.cron_interval', 'LIKE', '%'.$search_interval.'%');
                            } 
                        })                               
                        ->make(true);
    }

    public function logData(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_CRONS);

        if($checkrights) 
        {
            return $checkrights;
        }                

        $model = CronLogDetail::select("cron_log_detail.*","cron_log.cron_name as name" )
                ->join("cron_log","cron_log.id","=","cron_log_detail.cron_log_id");

        return Datatables::eloquent($model)
           
            ->editColumn('created_at', function($row){                
                if(!empty($row->created_at))
                    return date("j M, Y H:i:s",strtotime($row->created_at));
                else
                    return '-';    
            })
            ->editColumn('start_time', function($row){                
                if(!empty($row->start_time))
                    return date("j M, Y H:i:s",strtotime($row->start_time));
                else
                    return '-';    
            })
            ->editColumn('end_time', function($row){                
                if(!empty($row->end_time) && $row->end_time != '1970-01-01 00:00:00' && $row->end_time != '0000-00-00 00:00:00')
                    return date("j M, Y H:i:s",strtotime($row->end_time));
                else
                    return '-';    
            })
            ->editColumn('total_time', function($row){

                $start_time  = strtotime($row->start_time);
                $end_time = strtotime($row->end_time);
                $time = '-';
                if(!empty($end_time) || is_null($end_time))
                {
                    $diffInSeconds = $end_time - $start_time;
                    $min = $diffInSeconds/60;
                    if($min<1)
                        $time = $diffInSeconds.' Sec';
                    else
                        $time = round($min,3).' Min';
                }    

                return $time;
            })                       
            ->editColumn('summary', function($row){
                $summary = $row->summary;
                $html = '';
                if(!empty($summary))
                {
                    $summary = json_decode($summary,1);    

                    if(isset($summary['total']))                
                    $html .= '<b>Total Grab Records: </b>'.$summary['total']."<br />";

                    if(isset($summary['new']))
                    $html .= '<b>Added New Records: </b>'.$summary['new'];
                }
                return $html;  
            })   
            ->rawColumns(['summary', 'action'])             
            ->filter(function ($query) 
            {
                $search_start_date = trim(request()->get("search_start_date")); 
                $search_end_date = trim(request()->get("search_end_date")); 
                $search_id = request()->get("search_id");                                       
                $search_name = request()->get("search_name");                      
                $search_summary = request()->get("search_summary");
                $search_machine = request()->get("search_machine");                                         
                if (!empty($search_start_date)) 
                {
                    $from_date = $search_start_date.' 00:00:00';
                    $convertFromDate = $from_date;                         
                    $query = $query->where("cron_log_detail.created_at",">=",addslashes($convertFromDate));
                }
    
                if (!empty($search_end_date)) 
                {
                    $to_date=$search_end_date.' 23:59:59';
                    $convertToDate = $to_date;                        
                    $query = $query->where("cron_log_detail.created_at","<=",addslashes($convertToDate));
                }
                if(!empty($search_id))
                {
                    $idArr = explode(',', $search_id);
                    $idArr = array_filter($idArr);                
                    if(count($idArr)>0)
                    {
                        $query = $query->whereIn('cron_log_detail.id',$idArr);
                    } 
                }
                if(!empty($search_name))
                {
                    $query = $query->where('cron_log.id',$search_name);
                }                     
                if(!empty($search_summary))
                {
                    $query = $query->where('cron_log_detail.summary', 'LIKE', '%'.$search_summary.'%');
                } 
                if(!empty($search_machine))
                {
                    $query = $query->where('cron_log_detail.machine_id', 'LIKE', '%'.$search_machine.'%');
                } 
            })
            ->make(true);        
    }      

}
