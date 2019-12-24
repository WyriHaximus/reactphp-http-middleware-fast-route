<?php declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

final class Route
{
    /** @var string */
    private $method;

    /** @var string */
    private $route;

    /** @var callable */
    private $handler;

    /** @var array<string, mixed> */
    private $annotations = [];

    /**
     * @param string $method
     * @param string $route
     * @param callable $handler
     * @param array<string, mixed> $annotations
     */
    public function __construct(string $method, string $route, callable $handler, array $annotations)
    {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
        $this->annotations = $annotations;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function route(): string
    {
        return $this->route;
    }

    public function handler(): callable
    {
        return $this->handler;
    }

    /**
     * @return array<string, mixed>
     */
    public function annotations(): array
    {
        return $this->annotations;
    }
}
