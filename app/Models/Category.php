<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = TBL_CATEGORY;
    
    public static function getCategory($val, $parent_id)
    {
        $title = $val;
        $matchTitle = trim(strtolower($val));
        
        $model = Category::where(\DB::raw("TRIM(LOWER('title'))"),$matchTitle)->first();
        if($model)
        {
            return $model->id;
        }            
        else
        {
            $model = new Category();
            $model->title = $title;
            if($parent_id > 0)
            $model->parent_id = $parent_id;
            $model->save();
            
            return $model->id;
        }
    }
}
