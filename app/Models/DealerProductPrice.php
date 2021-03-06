<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerProductPrice extends Model
{
    protected $table = TBL_DEALER_PRODUCT_PRICES;
    
    public function dealerProduct()
    {
        return $this->belongsTo('App\Models\DealerProduct','id');
    }
}
