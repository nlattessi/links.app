<?php

use App\Transformers\LinkTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use League\Fractal\TransformerAbstract;

class LinkTransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function testItCanBeInitialized()
    {
        $transformer = new LinkTransformer();
        $this->assertInstanceOf(TransformerAbstract::class, $transformer);
    }
}
