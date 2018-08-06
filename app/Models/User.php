<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
   // public $timestamps = true;
    use Notifiable, Sluggable;
    protected $table = TBL_USERS;
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['firstname','lastname','email','city','image','state','country','zipcode','mobile'];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
   /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => array('firstname','lastname'),
                'on_update' => true
            ]
        ];
    }        
    
    
    public function getFullNameAttribute() { 
        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }    

    public function getImageUrl()
    {
        $image = $this->image;
        $imageUrl = asset("images/default-user.jpg");
        
        if(!empty($image) && !is_null($image))
        {
            $imageUrl = asset("uploads/users/".$this->id."/".$image);
        }
        
        return $imageUrl;
    }
    
    public function getAdminList($params) 
    {
        $from_date = isset($params['search_start_date']) ? trim($params['search_start_date']) : '';
        $to_date = isset($params['search_end_date']) ? trim($params['search_end_date']) : '';
        
        $sortBy = isset($params['sort_by']) ? $params['sort_by'] : '';
        $sortOrd = isset($params['sort_order']) ? $params['sort_order'] : 'DESC';
        
        $search_id = isset($params['search_id']) ? trim($params['search_id']) : '';
        $search_firstname = isset($params['search_firstname']) ? trim($params['search_firstname']) : '';
        $search_lastname = isset($params['search_lastname']) ? trim($params['search_lastname']) : '';
        $search_email = isset($params['search_email']) ? trim($params['search_email']) : '';


        $query = DB::table($this->table . ' AS u');

        // filter query         
        if(!empty($search_id))
        {
            $query->where('u.id', $search_id);
        }
        
        if(!empty($search_firstname))
        {
            $query->where('u.firstname', 'LIKE', '%' . addslashes($search_firstname) . '%');
        }                
        
        if(!empty($search_lastname))
        {
            $query->where('u.lastname', 'LIKE', '%' . addslashes($search_lastname) . '%');
        }                
        
        if(!empty($search_email))
        {
            $query->where('u.email', 'LIKE', '%' . addslashes($search_email) . '%');
        }                

        if (!empty($from_date)) 
        {
            $query->whereRaw("DATE_FORMAT(u.created_at, '%Y-%m-%d') >= '".  addslashes($from_date)."'");
        }
        
        if (!empty($to_date)) 
        {
            $query->whereRaw("DATE_FORMAT(u.created_at, '%Y-%m-%d') <= '".  addslashes($to_date)."'");
        }

        // sort query
        if ($sortBy != "" && $sortOrd != "") 
        {
            $query->orderBy($sortBy, $sortOrd);
        }
        else 
        {
            $query->orderBy('u.id', 'DESC');
        }

        $record_per_page = (isset($params['record_per_page']) && $params['record_per_page'] != "" && $params['record_per_page'] > 0) ? $params['record_per_page'] : env('APP_RECORDS_PER_PAGE', 20);

        return $query->paginate($record_per_page);
    }
    
}
