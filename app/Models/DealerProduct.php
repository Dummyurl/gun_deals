<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerProduct extends Model
{
    protected $table = TBL_DEALER_PRODUCTS;

    public function dealerProductAttribute()
    {
        return $this->hasMany('App\Models\DealerProductAttribute','product_id');
    }

    public function dealerProductPhoto()
    {
        return $this->hasMany('App\Models\DealerProductPhoto','product_id');
    }

    public function dealerProductPrice()
    {
        return $this->hasMany('App\Models\DealerProductPrice','product_id');
    }
}
