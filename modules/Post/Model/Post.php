<?php

namespace Modules\Post\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use DB;

class Post extends Model
{
    
    protected $table = 'posts';
    use SoftDeletes;
    use HasSlug;
    // protected $hidden = ['password','last_name', 'permissions', 'last_login', 'created_at', 'updated_at', 'pivot','deleted_at'];
    protected $appends = ['image'];
    
    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function admin()
    {
        return $this->belongsTo('Modules\User\Model\User', 'admin_id');
    }

    public function writer()
    {
        return $this->belongsTo('Modules\User\Model\User', 'writer_id');
    }

    public function editor()
    {
        return $this->belongsTo('Modules\User\Model\User', 'editor_id');
    }

    public function category()
    {
        return $this->belongsTo('Modules\Category\Model\Category', 'category_id');
    }

    public function comment()
    {
        return $this->hasMany('Modules\Comment\Model\Comment', 'post_id');
    }

    //=============scope query==============

    public function scopeSearch($query, $q, $result_type)
    {
        if ($q != null) {
            if ($result_type == 'slug') {
                $data = $query->where('slug','LIKE','%'.$q.'%');
            } else if ($result_type == 'id') {
                $data = $query->where('id',$q);
            } else if($result_type == 'title'){
                $data = $query->where(DB::Raw('lower(title)'),'LIKE','%'.$q.'%');
            } else if ($result_type == 'username') {
                $data = $query->whereHas('writer',function($result) {
                  $result->where('username',$q);
                });
            } else if ($result_type == 'first_name') {
                $data = $query->whereHas('writer',function($result) {
                      $result->where('first_name',$q);
                });
            } else if ($result_type == 'email') {
                $data = $query->whereHas('writer',function($result) {
                      $result->where('email',$q);
                });
            } else {
                $data = $query->where(DB::Raw('lower(title)'),'LIKE','%'.$q.'%');
            }
        } else {
            return null;
       // return $query->where(DB::Raw('lower(title)')); 
        }
        return $data;
    }

    public function scopeFilter($query, $filter)
    {
        if ($filter == 'pending') {
            $data = $query->where('status',0);
        } else if ($filter == 'publish') {
            $data = $query->where('status',1);
        } else if ($filter == 'draft') {
            $data = $query->where('status',2);
        } else if ($filter == 'suspend') {
            $data = $query->where('status',3);
        } else {
            $data = null;
        }
        return $data;
    }

    public function scopeByCategory($query,$category)
    {
        if ($category!= null) {
            $data = $query->whereHas('category', function($q) use ($category) {
                $q->where('slug',$category);
            });
        } else {
            $data = null;
        }
        return $data;
    }

    public function scopeFindBySlug($query, $slug)
    {
        return $query->where('slug',$slug)->first();
    }

    //=============end scope======================


    //=================get attriute ===========
    public function getImageAttribute()
    {
        if ($this->attributes['image'] != null) {
            return asset('storage/'.$this->attributes['image']);    
        } else {
            return asset('storage/default.gif');    
        }
        
    }
}
