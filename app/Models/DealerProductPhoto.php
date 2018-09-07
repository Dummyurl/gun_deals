<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerProductPhoto extends Model
{
    protected $table = TBL_DEALER_PRODUCT_PHOTOS;
    
    public function dealerProduct()
    {
        return $this->belongsTo('App\Models\DealerProduct','id');
    }
}
