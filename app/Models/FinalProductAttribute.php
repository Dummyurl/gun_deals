<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalProductAttribute extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function finalProduct()
    {
        return $this->belongsTo('App\Models\FinalProduct');
    }
}
