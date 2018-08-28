<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $cron_url
 * @property string $cron_name
 * @property string $cron_interval
 * @property string $created_at
 * @property string $updated_at
 * @property CronLogDetail[] $cronLogDetails
 */
class CronLog extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'cron_log';

    /**
     * @var array
     */
    protected $fillable = ['cron_url', 'cron_name', 'cron_interval', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cronLogDetails()
    {
        return $this->hasMany('App\Models\CronLogDetail');
    }
}
