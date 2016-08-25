<?php

use App\Transformers\CategoryTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use League\Fractal\TransformerAbstract;

class CategoryTransformerTest extends TestCase
{
    use DatabaseMigrations;

    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->subject = new CategoryTransformer();
    }

    /** @test **/
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(CategoryTransformer::class, $this->subject);
    }

    /** @test **/
    public function test_it_transforms_a_category_model()
    {
        $category = factory(\App\Category::class)->create();
        
        $transformedCategory = $this->subject->transform($category);

        $this->assertEquals($category->uuid, $transformedCategory['id']);
        $this->assertEquals($category->name, $transformedCategory['name']);
    }

    /** @test **/
    public function it_can_transform_related_links()
    {
        $link = $this->linkFactory();
        $category = $link->category;

        $data = $this->subject->includeLinks($category);
        $this->assertInstanceOf(\League\Fractal\Resource\Collection::class, $data);
    }
}
