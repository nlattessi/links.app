<?php

namespace App\Transformers;

use App\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'links'
    ];

    public function transform(Category $category)
    {
        return [
            'id' => $category->uuid,
            'name' => $category->name,
        ];
    }

    public function includeLinks(Category $category)
    {
        return $this->collection($category->links, new LinkTransformer());
    }
}
