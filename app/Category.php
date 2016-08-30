<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Uuids;

    protected $fillable = ['name', 'description'];

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
