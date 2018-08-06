<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {

    public $timestamps = true;
    protected $table = TBL_CITY;
    protected $fillable = ['title', 'state_id', 'country_id'];

}
