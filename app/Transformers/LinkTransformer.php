<?php

namespace App\Transformers;

use App\Link;
use League\Fractal\TransformerAbstract;

class LinkTransformer extends TransformerAbstract
{
    public function transform(Link $link)
    {
        return [
            'id' => $link->id,
            'uuid' => $link->uuid,
            'title' => $link->title,
            'url' => $link->url,
            'description' => $link->description,
            'category' => $link->category->name,
            'created_at' => $link->created_at->toDateTimeString(),
            'updated_at' => $link->updated_at->toDateTimeString(),
        ];
    }
}
