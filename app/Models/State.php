<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['title','country_id'];

    protected $table = TBL_STATE;
}
