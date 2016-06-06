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
            'title' => $link->title,
            'url' => $link->url,
            'description' => $link->description,
            'created_at' => $link->created_at->toIso8601String(),
            'updated_at' => $link->updated_at->toIso8601String(),
        ];
    }
}
