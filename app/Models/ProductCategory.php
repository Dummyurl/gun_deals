<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $menu_level
 * @property string $title
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 */
class ProductCategory extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['parent_id', 'menu_level', 'title', 'slug', 'created_at', 'updated_at'];


    public static $HANDSGUN_SEMI_AUTO = 8;

    public static $HANDSGUN_REVOLVER_DOUBLE_ACTION = 30;
    public static $HANDSGUN_REVOLVER_DOUBLE_ACTION_ONLY = 31;
    public static $HANDSGUN_REVOLVER_SINGLE_ACTION= 32;

    public static $HANDSGUN_SPC_DERRINGER = 33;
    public static $HANDSGUN_SPC_SINGLE_SHOT = 34;
    public static $HANDSGUN_SPC_LEVER_ACTION= 35;

    public static $RIFLES_SEMI_AUTO = 11;
    public static $RIFLES_BOLT_ACTION = 12;
    public static $RIFLES_LEVER_ACTION = 13;
    public static $RIFLES_SINGLE_SHOT = 14;
    public static $RIFLES_MUZZLE_LOADER = 15;
	public static $RIFLES_PUMP_ACTION = 16;

	public static $SHOTGUNS_SEMI_AUTO = 17;
	public static $SHOTGUNS_PUMP_ACTION = 18;
	public static $SHOTGUNS_SIDE_BY_SIDE = 19;
	public static $SHOTGUNS_OVER_UNDER = 20;
	public static $SHOTGUNS_LEVER_ACTION = 21;
	public static $SHOTGUNS_BOLT_ACTION = 22;
	public static $SHOTGUNS_SINGLE_SHOT = 23;
}
