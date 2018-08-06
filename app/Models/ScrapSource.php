<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $website_url
 * @property string $scrap_url
 * @property Deal[] $deals
 */
class ScrapSource extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'scrap_sources';

    /**
     * @var array
     */
    protected $fillable = ['title', 'website_url', 'scrap_url'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals()
    {
        return $this->hasMany('App\Models\Deal', 'source_id');
    }
}
