<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmmoProduct extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';
    protected $table = TBL_AMMO_PRODUCTS;

    public function product_category()
    {
        return $this->belongsTo('App\Models\ProductCategory');
    }

}
