<?php declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function FastRoute\simpleDispatcher;
use function React\Promise\resolve;
use function RingCentral\Psr7\stream_for;
use const WyriHaximus\Constants\HTTPStatusCodes\METHOD_NOT_ALLOWED;
use const WyriHaximus\Constants\HTTPStatusCodes\NOT_FOUND;
use const WyriHaximus\Constants\Numeric\ONE;
use const WyriHaximus\Constants\Numeric\TWO;
use const WyriHaximus\Constants\Numeric\ZERO;

final class FastRouteMiddleware
{
    /** @var Dispatcher */
    private $dispatcher;

    /** @var callable */
    private $notFoundHandler;

    /** @var callable */
    private $notAllowedHandler;

    /** @var Route[] */
    private $routes = [];

    /**
     * @param Route[] $routes
     * @param callable|null $notFoundHandler
     * @param callable|null $notAllowedHandler
     */
    public function __construct(array $routes, ?callable $notFoundHandler = null, ?callable $notAllowedHandler = null)
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routes): void {
            foreach ($routes as $route) {
                $routeCollector->addRoute($route->method(), $route->route(), $route->handler());
                $this->routes[spl_object_hash($route->handler())] = $route;
            }
        });
        $this->notFoundHandler = is_callable($notFoundHandler) ? $notFoundHandler : function (): ResponseInterface {
            return Factory::createResponse(NOT_FOUND)->
                withHeader('Content-Type', 'text/plain')->
                withBody(stream_for('Couldn\'t find what you\'re looking for'));
        };
        $this->notAllowedHandler = is_callable($notAllowedHandler) ? $notAllowedHandler : function (array $allowedMethods): ResponseInterface {
            return Factory::createResponse(METHOD_NOT_ALLOWED)->
                withHeader('Allow', \implode(', ', $allowedMethods))->
                withHeader('Content-Type', 'text/plain')->
                withBody(stream_for('Method not allowed'));
        };
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $route = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[ZERO] === Dispatcher::NOT_FOUND) {
            return resolve(($this->notFoundHandler)());
        }

        if ($route[ZERO] === Dispatcher::METHOD_NOT_ALLOWED) {
            return resolve(($this->notAllowedHandler)($route[ONE]));
        }

        foreach ($route[TWO] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request
            ->withAttribute(Attributes::HANDLER, $route[ONE])
            ->withAttribute(Attributes::ANNOTATIONS, $this->routes[spl_object_hash($route[ONE])]->annotations())
        ;

        return resolve($next($request));
    }
}
