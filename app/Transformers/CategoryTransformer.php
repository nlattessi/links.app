<?php

namespace App\Transformers;

use App\Category;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    private $validParams = ['order'];

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
        if ($params === null) {
            return $this->collection($category->links, new LinkTransformer());
        }

        // Optional params validation
        $usedParams = array_keys(iterator_to_array($params));
        if ($invalidParams = array_diff($usedParams, $this->validParams)) {
            throw new \Exception(sprintf(
                'Invalid param(s): "%s". Valid param(s): "%s"',
                implode(',', $usedParams),
                implode(',', $this->validParams)
            ));
        }

        // TODO: Validate order params
        list($orderCol, $orderBy) = $params->get('order');

        $links = $category->links()
            ->orderBy($orderCol, $orderBy)
            ->get();

        return $this->collection($links, new LinkTransformer());
    }
}
