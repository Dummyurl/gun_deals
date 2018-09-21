<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property int $product_category_id
 * @property string $product_id
 * @property integer $map_id
 * @property string $title
 * @property string $description
 * @property string $image
 * @property string $thumb_image
 * @property string $brand
 * @property string $model
 * @property string $mpn
 * @property string $upc_number
 * @property string $msrp
 * @property string $created_at
 * @property string $updated_at
 * @property ProductCategory $productCategory
 * @property FinalProductAttribute[] $finalProductAttributes
 */
class FinalProduct extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['product_category_id', 'product_id', 'map_id', 'title', 'description', 'image', 'thumb_image', 'brand', 'model', 'mpn', 'upc_number', 'msrp', 'created_at', 'updated_at','category'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productCategory()
    {
        return $this->belongsTo('App\ProductCategory');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function finalProductAttributes()
    {
        return $this->hasMany('App\FinalProductAttribute', 'product_id');
    }
}
