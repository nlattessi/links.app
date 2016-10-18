<?php

namespace App\Transformers;

use App\Category;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    private $validParams = ['order']:

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

    public function includeLinks(Category $category, ParamBag $params = null)
    {
        return $this->collection($category->linksByTitle, new LinkTransformer());
    }
}
