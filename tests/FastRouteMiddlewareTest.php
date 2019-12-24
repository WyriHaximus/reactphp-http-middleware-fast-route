<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use Closure;
use Middlewares\Utils\Factory as ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Http\Middleware\Attributes;
use WyriHaximus\React\Http\Middleware\FastRouteMiddleware;
use WyriHaximus\React\Http\Middleware\Route;
use function React\Promise\resolve;
use const WyriHaximus\Constants\HTTPStatusCodes\METHOD_NOT_ALLOWED;
use const WyriHaximus\Constants\HTTPStatusCodes\NOT_FOUND;

/**
 * @internal
 */
final class FastRouteMiddlewareTest extends AsyncTestCase
{
    /** @var ServerRequestInterface|null $passedRequest */
    private $passedRequest;

    /** @var callable */
    private $middleware;

    /** @var callable */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passedRequest = null;
        $this->middleware = function (?callable $notFoundHandler = null, ?callable $notAllowedHandler = null): FastRouteMiddleware {
            return new FastRouteMiddleware([
                new Route('GET', '/user/{name}/{id:[0-9]+}', function (ServerRequestInterface $request): PromiseInterface {
                    $this->passedRequest = $request;

                    return resolve(new Response(200));
                }, [
                    'childprocess' => false,
                    'coroutine' => false,
                    'thread' => false,
                ]),
                new Route('PUT', '/user/{name}/{id:[0-9]+}', function (ServerRequestInterface $request): PromiseInterface {
                    $this->passedRequest = $request;

                    return resolve(new Response(200));
                }, []),
            ], $notFoundHandler, $notAllowedHandler);
        };

        $this->handler = function (ServerRequestInterface $request) {
            return $request->getAttribute(Attributes::HANDLER)($request);
        };
    }

    public function test200(): void
    {
        $loop = Factory::create();

        $request = new ServerRequest(
            'GET',
            'https://example.com/user/nikic/42'
        );

        /** @var ResponseInterface $response */
        $response = $this->await(
            (($this->middleware)())($request, $this->handler),
            $loop
        );

        self::assertInstanceOf(RequestInterface::class, $this->passedRequest);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([
            'childprocess' => false,
            'coroutine' => false,
            'thread' => false,
        ], $this->passedRequest->getAttribute(Attributes::ANNOTATIONS));
        self::assertSame('nikic', $this->passedRequest->getAttribute('name'));
        self::assertSame('42', $this->passedRequest->getAttribute('id'));
    }

    public function provide404(): iterable
    {
        yield 'default' => [null];
        yield 'custom' => [function () {
            return ResponseFactory::createResponse(NOT_FOUND);
        }];
    }

    /**
     * @dataProvider provide404
     *
     * @param Closure(): PromiseInterface $notFoundHandler
     */
    public function test404($notFoundHandler): void
    {
        $loop = Factory::create();

        $request = new ServerRequest(
            'GET',
            'https://example.com/'
        );

        /** @var ResponseInterface $response */
        $response = $this->await(
            (($this->middleware)($notFoundHandler))($request, $this->handler),
            $loop
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function provide405(): iterable
    {
        yield 'default' => [null];
        yield 'custom' => [function (array $allowedMethods) {
            return ResponseFactory::createResponse(METHOD_NOT_ALLOWED)->
                withHeader('Allow', \implode(', ', $allowedMethods));
        }];
    }

    /**
     * @dataProvider provide405
     *
     * @param Closure(): PromiseInterface $notAllowedHandler
     */
    public function test405($notAllowedHandler): void
    {
        $loop = Factory::create();

        $request = new ServerRequest(
            'POST',
            'https://example.com/user/nikic/42'
        );

        /** @var ResponseInterface $response */
        $response = $this->await(
            (($this->middleware)(null, $notAllowedHandler))($request, $this->handler),
            $loop
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, PUT', $response->getHeaderLine('Allow'));
    }
}
