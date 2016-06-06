<?php

use App\Http\Response\FractalResponse;
use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;
use Mockery as m;

class FractalResponseTest extends TestCase
{
    public function test_it_can_be_initialized()
    {
        $manager = m::mock(Manager::class);
        $serializer = m::mock(SerializerAbstract::class);

        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $fractal = new FractalResponse($manager, $serializer);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }
}
