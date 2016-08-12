<?php

use App\Http\Response\FractalResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use Mockery as m;

class FractalResponseTest extends TestCase
{
    /** @test **/
    public function it_can_be_initialized()
    {
        $serializer = m::mock(SerializerAbstract::class);
        $request = m::mock(Request::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $fractal = new FractalResponse($manager, $serializer, $request);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }

    /** @test **/
    public function it_can_transform_an_item()
    {
        $serializer = m::mock(SerializerAbstract::class);
        $request = m::mock(Request::class);
        $transformer = m::mock(TransformerAbstract::class);

        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->twice()
            ->andReturn(['foo' => 'bar']);


        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $manager
            ->shouldReceive('createData')
            ->twice()
            ->andReturn($scope);

        $fractal = new FractalResponse($manager, $serializer,  $request);
        $this->assertInternalType(
            'array',
            $fractal->item(['foo' => 'bar'], $transformer)
        );
        $this->assertEquals(
            ['foo' => 'bar'],
            $fractal->item(['foo' => 'bar'], $transformer)
        );
    }

    /** @test **/
    public function it_can_transform_a_collection()
    {
        $data = [
            ['foo' => 'bar'],
            ['fizz' => 'buzz'],
        ];

        $serializer = m::mock(SerializerAbstract::class);
        $request = m::mock(Request::class);
        $transformer = m::mock(TransformerAbstract::class);

        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->twice()
            ->andReturn($data);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $manager
            ->shouldReceive('createData')
            ->twice()
            ->andReturn($scope);

        $fractal = new FractalResponse($manager, $serializer, $request);
        $this->assertInternalType(
            'array',
            $fractal->collection($data, $transformer)
        );
        $this->assertEquals(
            $data,
            $fractal->collection($data, $transformer)
        );
    }

    /** @test **/
    public function it_should_parse_passed_includes_when_passed()
    {
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $manager
            ->shouldReceive('parseIncludes')
            ->with('links')
            ->once();

        $request = m::mock(Request::class);
        $request->shouldNotReceive('query');

        $fractal = new FractalResponse($manager, $serializer, $request);
        $fractal->parseIncludes('links');
    }

    /** @test **/
    public function it_should_parse_request_query_includes_with_no_arguments()
    {
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $manager
            ->shouldReceive('parseIncludes')
            ->with('links')
            ->once();

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('query')
            ->with('include', '')
            ->once()
            ->andReturn('links');

        $fractal = new FractalResponse($manager, $serializer, $request);
        $fractal->parseIncludes();
    }
}
