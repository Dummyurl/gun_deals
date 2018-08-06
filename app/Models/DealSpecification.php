<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $deal_id
 * @property string $record_type
 * @property string $key
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 * @property Deal $deal
 */
class DealSpecification extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'deal_specifications';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['deal_id', 'record_type', 'key', 'value', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deal()
    {
        return $this->belongsTo('App\Models\Deal');
    }
}
