<?php

namespace App\Models\Models;

use App\Models\Models\BlogCategory;
use App\Models\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
     use HasFactory, SoftDeletes;
     const UNKNOWN_USER = 1;
     protected $fillable
             = [
                 'title',
                 'slug',
                 'category_id',
                 'excerpt',
                 'content_raw',
                 'is_published',
                 'published_at',
             ];

         /**
          * Категорія статті
          *
          * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
          */
         public function category()
         {
             //стаття належить категорії
             return $this->belongsTo(BlogCategory::class);
         }

         /**
          * Автор статті
          *
          * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
          */
         public function user()
         {
             //стаття належить користувачу
             return $this->belongsTo(User::class);
         }
}
