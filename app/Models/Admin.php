<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $guard = "admins";
    protected $table = TBL_ADMIN_USERS;
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'phone', 'created_at', 'updated_at'];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ]; 

    // admin group pages varibales
    public static $error_msg = "You are not authorised to view this page.";
    public static $LIST_ADMIN_LOG_ACTIONS = 1;
    public static $ADD_ADMIN_LOG_ACTIONS = 2;
    public static $EDIT_ADMIN_LOG_ACTIONS = 3;
    public static $DELETE_ADMIN_LOG_ACTIONS = 4;

    public static $LIST_ADMIN_MODULES = 5;
    public static $ADD_ADMIN_MODULES = 6;
    public static $EDIT_ADMIN_MODULES = 7;
    public static $DELETE_ADMIN_MODULES = 8;
    
    public static $LIST_ADMIN_MODULES_PAGES = 9;
    public static $ADD_ADMIN_MODULES_PAGES = 10;
    public static $EDIT_ADMIN_MODULES_PAGES = 11;
    public static $DETELE_ADMIN_MODULES_PAGES = 12;

    public static $LIST_USERS = 13;     
    public static $ADD_USERS = 14;
    public static $EDIT_USERS = 15; 
    public static $DELETE_USERS = 16;
    
    public static $LIST_USERS_ACTIONS = 17;
    public static $ADD_USERS_ACTIONS = 18;
    public static $EDIT_USERS_ACTIONS = 19;
    public static $DELETE_USERS_ACTIONS = 20;

    public static $ADMIN_USERS_LOG = 21;
    public static $ASSIGN_RIGHTS = 22;

//    public static $LIST_COUNTRIES = 23;
//    public static $ADD_COUNTRIES = 24;
//    public static $EDIT_COUNTRIES = 25;
//    public static $DELETE_COUNTRIES = 26;
//
//    public static $LIST_STATE = 27;
//    public static $ADD_STATE = 28;
//    public static $EDIT_STATE = 29;
//    public static $DELETE_STATE = 30;
//
//    public static $LIST_CITY = 31;
//    public static $ADD_CITY = 32;
//    public static $EDIT_CITY = 33;
//    public static $DELETE_CITY = 34;
    
    public static $LIST_ADMIN_USERS = 39;
    public static $ADD_ADMIN_USERS = 40;
    public static $EDIT_ADMIN_USERS = 41;
    public static $DELETE_ADMIN_USERS = 42;
    
    public static $LIST_DEAL_SOURCES = 43;
    public static $ADD_DEAL_SOURCE = 44;
    public static $EDIT_DEAL_SOURCE = 45;
    public static $DELETE_DEAL_SOURCE = 46;
    
    public static $LIST_DEALS = 47;
    public static $ADD_DEAL = 48;
    public static $EDIT_DEAL = 49;
    public static $DELETE_DEAL = 50;

    public static $LIST_GALLERYGUNS = 51;
    public static $EDIT_GALLERYGUNS = 52;

    public static $LIST_CRONS = 53;
    public static $ADD_CRON = 54;
    public static $EDIT_CRON = 55;
    public static $DELETE_CRON = 56;
    public static $LIST_CRON_LOG = 57;

    public static $ADD_GALLERYGUNS = 58;
    public static $DELETE_GALLERYGUNS = 59;

    public static $LIST_AMMO_PRODUCTS = 60;

    public static $LIST_SCRAP_URLS = 61;
    public static $ADD_SCRAP_URL = 62;
    public static $EDIT_SCRAP_URL = 63;
    public static $DELETE_SCRAP_URL = 64;
  


    /**
     * check page acces permissions
     *          
     * @var $page_id
     */
    public static function checkPermission($intCurAdminUserRight)
    {
        $userrights = session("admin_user_rights_ids");
        if(is_array($userrights) && !empty($userrights)){
            if (!in_array($intCurAdminUserRight, (array)$userrights)) {
                session()->flash('error_message',\App\Models\Admin::$error_msg);
                return redirect('admin/dashboard');
            }
        }
    }
    /**
     * check page acces permissions
     *
     * @var $page_id
     */        
    public static function isAccess($page_id)
    {
        $array = session("admin_user_rights_ids");
        $status = 0;
        
        if(is_array($array) && in_array($page_id, $array))
        {
            $status = 1;
        }
        return $status;
    }   
}
