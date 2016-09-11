<?php

use App\Exceptions\Handler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_responds_with_html_when_json_is_not_accepted()
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

    public function test_it_responds_with_json_when_json_is_accepted()
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
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $error['status']);
    }

    public function test_it_responds_with_json_responses_for_http_exceptions()
    {
        $handler = m::mock(Handler::class)->makePartial();
        $handler->shouldReceive('isDebugMode')->andReturn(false);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exceptions = [
            [
                'mock' => AccessDeniedHttpException::class,
                'message' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'status' => Response::HTTP_FORBIDDEN,
            ],
            [
                'mock' => NotFoundHttpException::class,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'status' => Response::HTTP_NOT_FOUND,
            ],
            [
                'mock' => ModelNotFoundException::class,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'status' => Response::HTTP_NOT_FOUND,
            ],
            [
                'mock' => \Tymon\JWTAuth\Exceptions\JWTException::class,
                'message' => 'token_exception',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            [
                'mock' => \Tymon\JWTAuth\Exceptions\TokenExpiredException::class,
                'message' => 'token_expired',
                'status' => Response::HTTP_UNAUTHORIZED,
            ],
            [
                'mock' => \Tymon\JWTAuth\Exceptions\TokenInvalidException::class,
                'message' => 'token_invalid',
                'status' => Response::HTTP_UNAUTHORIZED,
            ],
            [
                'mock' => \Ramsey\Uuid\Exception\UnsatisfiedDependencyException::class,
                'message' => '',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            [
                'mock' => \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException::class,
                'message' => '',
                'status' => Response::HTTP_UNAUTHORIZED,
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
