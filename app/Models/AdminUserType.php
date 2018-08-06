<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property AdminGroupPage[] $adminGroupPages
 */
class AdminUserType extends Model
{
    protected $table = TBL_ADMIN_USER_TYPE;
    /**
     * @var array
     */
    protected $fillable = ['title'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function adminGroupPages()
    {
        return $this->belongsToMany('App\Models\AdminGroupPage', 'admin_user_rights', 'user_type_id', 'page_id');
    }
}
