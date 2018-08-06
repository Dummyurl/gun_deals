<?php
namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\Admin;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->adminAction = new \App\Models\AdminAction;
    }

    public function index(Request $request)
    {                          
        $data = array();              
        return view('admin.dashboard',$data);
    }    
    
    public function changePassword()
    {        
        $data = array();
        return view('admin.changepwd',$data);        
    }    
    
    // post change password
    public function postChangePassword(Request $request)
    {        
        $status = 1;
        $msg = "Your password has been changed successfully.";
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:4',
            'new_password' => 'required|min:4|confirmed',
            'new_password_confirmation' => 'required',
        ]);        
        
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
            $user = Auth::guard("admins")->user();
            
            $old_password = $request->get('password');
            
            if(Hash::check($old_password, $user->password))
            {
                $user->password = bcrypt($request->get('new_password'));
                $user->save();
                
                // save log
                $params=array();

                $params['adminuserid']	= $user->id;
                $params['actionid']	= $this->adminAction->UPDATE_CHANGE_PASSWORD;
                $params['actionvalue']	= $user->id;
                $params['remark']	= 'Change Password';

                \App\Models\AdminLog::writeadminlog($params);
                unset($params);                                
            }
            else
            {
                $status = 0;
                $msg = 'old password is incorrect.';
            }
        }       
        
        
        return ['status' => $status, 'msg' => $msg];
    }    
    
    // edit profile
    public function editProfile()
    {        
        $data = array();
        $data['formObj'] = \Auth::guard("admins")->user();

        
        return view('admin.profile',$data);        
    }    
    
    // update profile
    public function updateProfile(Request $request)
    {        
        $status = 1;
        $msg = "Your profile has been updated successfully.";
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:admin_users,email,'.\Auth::guard("admins")->user()->id,
            'name' => 'required|min:2|max:255',
        ]);        
        
        
        if($validator->fails())
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
            $user = \Auth::guard("admins")->user();            
            $input['name'] = $request->get("name");            
            $input['email'] = $request->get("email");
            $input['phone'] = $request->get("phone");            
            $user->update($input);             
            
            // save log
            $params=array();

            $params['adminuserid']	= $user->id;
            $params['actionid']	= $this->adminAction->UPDATE_PROFILE;
            $params['actionvalue']	= $user->id;
            $params['remark']	= 'Update Profile';

            \App\Models\AdminLog::writeadminlog($params);
            unset($params);                
            
        }
        
        
        return ['status' => $status, 'msg' => $msg];
    }
    public function rights(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ASSIGN_RIGHTS);
        
        if($checkrights) 
        {
            return $checkrights;
        }        
        
        $type_id = $request->get("type_id");


        if($request->isXmlHttpRequest() && $request->get("action") == "update")
        {
            $status = 1;
            $msg = "Rights has been updated successfully.";

            if(intval($type_id) > 0)
            {
                $ids = $request->get("ids");
                
                // Delete old Roles
                \DB::table(TBL_ADMIN_USER_RIGHT)->where("user_type_id", $type_id)->delete();

                if(is_array($ids) && count($ids) > 0)
                {
                    foreach($ids as $page_id)
                    {
                        $dataToInsert = [
                            'user_type_id' => $type_id,
                            'page_id' => $page_id
                        ];
                        
                        \DB::table(TBL_ADMIN_USER_RIGHT)->insert($dataToInsert);

                        unset($dataToInsert);
                    }    
                }                
                
                //store logs detail
                $params=array();                                            
                $params['adminuserid']  = \Auth::guard('admins')->id();
                $params['actionid']     = $this->adminAction->UPDATE_RIGHTS;
                $params['actionvalue']  = $type_id;
                $params['remark']       = "Update Rights ::".$type_id;                                        
                \App\Models\AdminLog::writeadminlog($params);                
            }
            else
            {
                $status = 0;
                $msg = "Please select user type.";
            }

            return ['status' => $status, 'msg' => $msg];
        }


        $data = array();
        $data['roles'] = \App\Models\AdminUserType::get();
        $data['ids_selected'] = array();

        if(intval($type_id) > 0)
        {
            $temp = \App\Models\AdminUserRight::where("user_type_id",$type_id)->get();
            foreach($temp as $r)
            {
                $data['ids_selected'][] = $r->page_id;
            }    
        }

        $ADMIN_GROUPS = TBL_ADMIN_GROUP;
        $ADMIN_GROUP_PAGES = TBL_ADMIN_GROUP_PAGE;

        $query= " SELECT ".
                  $ADMIN_GROUPS.".id AS trngroupid, ".
                  $ADMIN_GROUPS.".title AS trngrouptitle, ".
                  $ADMIN_GROUP_PAGES.".id AS trnid, ".
                  $ADMIN_GROUP_PAGES.".name AS trnname, ".
                  $ADMIN_GROUP_PAGES.".url AS pageurl, ".
                  $ADMIN_GROUP_PAGES.".menu_title AS trntitle, ".
                  $ADMIN_GROUP_PAGES.".show_in_menu AS show_in_menu, ".                  
                  $ADMIN_GROUP_PAGES.".is_sub_menu AS insubmenu ".
            " FROM ".
                  $ADMIN_GROUPS.", ".
                  $ADMIN_GROUP_PAGES." ".                  
            " WHERE ".
                  $ADMIN_GROUPS.".id = ".$ADMIN_GROUP_PAGES.".admin_group_id".
            " ORDER BY ".
                  $ADMIN_GROUPS.".order_index, ".
                  $ADMIN_GROUPS.".title, ".
                  $ADMIN_GROUP_PAGES.".menu_order, ".
                  $ADMIN_GROUP_PAGES.".name";


        $rows = \DB::select($query);
        $rows = json_decode(json_encode($rows), true);
        $data['rows'] = $rows;

        return view('admin.rights',$data);        
    }    
}
