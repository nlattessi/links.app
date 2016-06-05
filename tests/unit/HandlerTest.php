<?php

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItRespondsWithHtmlWhenJsonIsNotAccepted()
    {
        $handler = m::mock(Handler::class)->makePartial();
        $handler->shouldNotReceive('isDebugMode');

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(false);

        $exception = m::mock(\Exception::class, ['Error']);
        $exception->shouldNotReceive('getStatusCode');
        $exception->shouldNotReceive('getTrace');
        $exception->shouldNotReceive('getMessage');

        $result = $handler->render($request, $exception);
        $this->assertNotInstanceOf(JsonResponse::class, $result);
    }

    public function testItRespondsWithJsonWhenJsonIsAccepted()
    {
        $handler = m::mock(Handler::class)->makePartial();
        $handler->shouldReceive('isDebugMode')->andReturn(false);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = m::mock(\Exception::class, ['Error']);
        $exception->shouldReceive('getMessage')->andReturn('Error');

        $result = $handler->render($request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $result);

        $data = $result->getData(true);
        $this->assertArrayHasKey('error', $data);

        $error = $data['error'];
        $this->assertArrayHasKey('message', $error);
        $this->assertArrayHasKey('status', $error);
        $this->assertEquals('Error', $error['message']);
        $this->assertEquals(400, $error['status']);
    }

    public function testItRespondsWithJsonResponsesForHttpExceptions()
    {
        $handler = m::mock(Handler::class)->makePartial();
        $handler->shouldReceive('isDebugMode')->andReturn(false);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exceptions = [
            [
                'mock' => AccessDeniedHttpException::class,
                'message' => 'Forbidden',
                'status' => 403,
            ],
            [
                'mock' => NotFoundHttpException::class,
                'message' => 'Not Found',
                'status' => 404,
            ],
        ];

        foreach ($exceptions as $e) {
            $exception = m::mock($e['mock']);
            $exception->shouldReceive('getMessage')->andReturn($e['message']);
            $exception->shouldReceive('getStatusCode')->andReturn($e['status']);

            $result = $handler->render($request, $exception);
            $this->assertInstanceOf(JsonResponse::class, $result);

            $data = $result->getData(true);
            $this->assertArrayHasKey('error', $data);

            $error = $data['error'];
            $this->assertArrayHasKey('message', $error);
            $this->assertArrayHasKey('status', $error);
            $this->assertEquals($e['message'], $error['message']);
            $this->assertEquals($e['status'], $error['status']);
            $this->assertEquals($e['status'], $result->getStatusCode());
        }
    }
}
