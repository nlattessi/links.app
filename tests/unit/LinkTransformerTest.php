<?php

use App\Transformers\LinkTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use League\Fractal\TransformerAbstract;

class LinkTransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_can_be_initialized()
    {
        $this->assertInstanceOf(TransformerAbstract::class, new LinkTransformer());
    }

    public function test_it_transforms_a_link_model()
    {
        $link = $this->linkFactory();
        $transformer = new LinkTransformer();

        $transformedLink = $transformer->transform($link);

        $this->assertArrayHasKey('id', $transformedLink);
        $this->assertArrayHasKey('uuid', $transformedLink);
        $this->assertArrayHasKey('title', $transformedLink);
        $this->assertArrayHasKey('url', $transformedLink);
        $this->assertArrayHasKey('description', $transformedLink);
        $this->assertArrayHasKey('category', $transformedLink);
    }
}
