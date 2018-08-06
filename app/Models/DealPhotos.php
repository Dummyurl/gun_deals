<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $deal_id
 * @property string $filename
 * @property string $image_url
 * @property string $created_at
 * @property string $updated_at
 * @property Deal $deal
 */
class DealPhotos extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'deal_photos';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['deal_id', 'filename', 'image_url', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deal()
    {
        return $this->belongsTo('App\Models\Deal');
    }
}
