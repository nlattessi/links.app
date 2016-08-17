<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use Uuids;

    protected $fillable = ['title', 'url', 'description', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
