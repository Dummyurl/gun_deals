<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property int $source_id
 * @property string $title
 * @property string $description
 * @property string $image
 * @property string $link
 * @property string $ratings
 * @property int $reviews_count
 * @property float $base_price
 * @property float $sale_price
 * @property float $save_price
 * @property string $from_url
 * @property string $unique_md5
 * @property boolean $out_of_stock
 * @property string $created_at
 * @property string $updated_at
 * @property ScrapSource $scrapSource
 * @property DealPhoto[] $dealPhotos
 * @property DealSpecification[] $dealSpecifications
 */
class Deal extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'deals';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['source_id', 'title', 'description', 'image', 'link', 'ratings', 'reviews_count', 'base_price', 'sale_price', 'save_price', 'from_url', 'unique_md5', 'out_of_stock', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scrapSource()
    {
        return $this->belongsTo('App\Models\ScrapSource', 'source_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dealPhotos()
    {
        return $this->hasMany('App\Models\DealPhotos');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dealSpecifications()
    {
        return $this->hasMany('App\Models\DealSpecification');
    }
}
