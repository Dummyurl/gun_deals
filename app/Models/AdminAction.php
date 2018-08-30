<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $description
 * @property string $remark     
 */
class AdminAction extends Model
{
    public $timestamps = false;
    protected $table = TBL_ADMIN_ACTION;
    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['description', 'remark','id'];
    
    /**
     *
     * Activity Constants
     *
     */
    public $ADMIN_LOGIN = 1;
    public $ADMIN_LOGOUT = 2;
    public $UPDATE_PROFILE = 3;
    public $UPDATE_CHANGE_PASSWORD = 4;
    
    public $ADD_ADMIN_ACTION = 5;
    public $EDIT_ADMIN_ACTION = 6;
    public $DELETE_ADMIN_ACTION = 7;

    public $ADD_USER_ACTION = 8;    
    public $EDIT_USER_ACTION = 9;    
    public $DELETE_USER_ACTION = 10; 
    
    public $ADD_ADMIN_MODULES_PAGES = 11;
    public $EDIT_ADMIN_MODULES_PAGES = 12;
    public $DETELE_ADMIN_MODULES_PAGES = 13;

    public $UPDATE_RIGHTS = 14;   

    public $ADD_ADMIN_MODULES = 15;  
    public $EDIT_ADMIN_MODULES = 16;
    public $DELETE_ADMIN_MODULES = 17;

    public $ADD_COUNTRIES = 18;
    public $EDIT_COUNTRIES = 19;
    public $DELETE_COUNTRIES = 20;

    public $ADD_STATE = 21;
    public $EDIT_STATE = 22;
    public $DELETE_STATE = 23;

    public $ADD_CITY = 24;
    public $EDIT_CITY = 25;
    public $DELETE_CITY = 26;
    
    public $ADD_ADMIN_USERS = 27;
    public $EDIT_ADMIN_USERS = 28;
    public $DELETE_ADMIN_USERS = 29;   
    
    public $ADD_DEAL_SOURCE = 30;
    public $EDIT_DEAL_SOURCE = 31;
    public $DELETE_DEAL_SOURCE = 32;

    public $EDIT_PRODUCT = 33;   

    public $ADD_CRON = 34;
    public $EDIT_CRON = 35;
    public $DELETE_CRON = 36;    

    public $ADD_PRODUCT = 37;
    public $DELETE_PRODUCT = 38;
}
