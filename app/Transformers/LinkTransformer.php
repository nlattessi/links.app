<?php

namespace App\Transformers;

use App\Link;
use League\Fractal\TransformerAbstract;

class LinkTransformer extends TransformerAbstract
{
    public function transform(Link $link)
    {
        return [
            'id' => $link->uuid,
            'title' => $link->title,
            'url' => $link->url,
            'description' => $link->description,
            'category' => $link->category->name,
        ];
    }
}
