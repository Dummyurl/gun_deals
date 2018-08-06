<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
       protected $fillable = ['id','user_type_id','name','email','password'];
       public $timestamps = true;
       protected $table = TBL_ADMIN_USERS;

    

    

}
