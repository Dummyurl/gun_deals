<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property int $cron_log_id
 * @property string $start_time
 * @property string $end_time
 * @property string $summary
 * @property string $machine_id
 * @property string $created_at
 * @property CronLog $cronLog
 */
class CronLogDetail extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'cron_log_detail';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['cron_log_id', 'start_time', 'end_time', 'summary', 'machine_id', 'created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cronLog()
    {
        return $this->belongsTo('App\Models\CronLog');
    }
}
