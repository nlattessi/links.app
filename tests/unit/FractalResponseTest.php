<?php

use App\Http\Response\FractalResponse;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use Mockery as m;

class FractalResponseTest extends TestCase
{
    public function test_it_can_be_initialized()
    {
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $fractal = new FractalResponse($manager, $serializer);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }

    public function test_it_can_transform_an_item()
    {
        $transformer = m::mock(TransformerAbstract::class);

        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->twice()
            ->andReturn(['foo' => 'bar']);

        $serializer = m::mock(SerializerAbstract::class);

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

        $fractal = new FractalResponse($manager, $serializer);
        $this->assertInternalType(
            'array',
            $fractal->item(['foo' => 'bar'], $transformer)
        );
        $this->assertEquals(
            ['foo' => 'bar'],
            $fractal->item(['foo' => 'bar'], $transformer)
        );
    }

    public function test_it_can_transform_a_collection()
    {
        $data = [
            ['foo' => 'bar'],
            ['fizz' => 'buzz'],
        ];

        $transformer = m::mock(TransformerAbstract::class);

        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->twice()
            ->andReturn($data);

        $serializer = m::mock(SerializerAbstract::class);

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

        $fractal = new FractalResponse($manager, $serializer);
        $this->assertInternalType(
            'array',
            $fractal->collection($data, $transformer)
        );
        $this->assertEquals(
            $data,
            $fractal->collection($data, $transformer)
        );
    }
}
