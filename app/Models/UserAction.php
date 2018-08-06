<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $description
 * @property string $remark     
 */
class UserAction extends Model
{
    public $timestamps = false;
    protected $table = TBL_USERS_ACTION;
    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['description', 'remark'];
    
    /**
     *
     * Activity Constants
     *
     */
    
}
