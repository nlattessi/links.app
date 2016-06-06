<?php

use App\Transformers\LinkTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use League\Fractal\TransformerAbstract;

class LinkTransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function testItCanBeInitialized()
    {
        $this->assertInstanceOf(TransformerAbstract::class, new LinkTransformer());
    }

    public function test_it_transforms_a_link_model()
    {
        $link = factory(\App\Link::class)->create();
        $transformer = new LinkTransformer();

        $transformedLink = $transformer->transform($link);

        $this->assertArrayHasKey('id', $transformedLink);
        $this->assertArrayHasKey('title', $transformedLink);
        $this->assertArrayHasKey('url', $transformedLink);
        $this->assertArrayHasKey('description', $transformedLink);
        $this->assertArrayHasKey('created_at', $transformedLink);
        $this->assertArrayHasKey('updated_at', $transformedLink);
    }
}
