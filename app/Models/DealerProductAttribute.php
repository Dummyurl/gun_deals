<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerProductAttribute extends Model
{
    protected $table = TBL_DEALER_PRODUCT_ATTRIBUTES;
    
    public function dealerProduct()
    {
        return $this->belongsTo('App\Models\DealerProduct','id');
    }
}
