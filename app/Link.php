<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use Uuids;

    protected $fillable = ['title', 'url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
