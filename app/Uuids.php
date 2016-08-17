<?php

namespace App;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

trait Uuids
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
