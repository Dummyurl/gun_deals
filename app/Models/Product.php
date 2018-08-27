<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $product_id
 * @property string $item_id
 * @property string $category
 * @property string $title
 * @property string $link
 * @property string $link_md5
 * @property string $image
 * @property string $thumb_image
 * @property string $item_unique_id
 * @property string $brand
 * @property string $model
 * @property string $upc_number
 * @property string $msrp
 * @property string $created_at
 * @property string $updated_at
 * @property ProductAttribute[] $productAttributes
 */
class Product extends Model
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
    protected $fillable = ['product_id', 'item_id', 'category', 'title', 'link', 'link_md5', 'image', 'thumb_image', 'item_unique_id', 'brand', 'model', 'upc_number', 'msrp', 'created_at', 'updated_at','description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productAttributes()
    {
        return $this->hasMany('App\Models\ProductAttribute');
    }
}
