<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\Command;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

final class HttpServer implements Command
{
    const COMMAND = 'http-server';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $address;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var string
     */
    private $public;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     * @param string $address
     * @param callable $handler
     * @param string $public
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger, string $address, callable $handler, string $public = null)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->address = $address;
        $this->handler = $handler;
        $this->public = $public;
    }

    public function __invoke()
    {
        $socket = new SocketServer($this->address, $this->loop);
        $middleware = [];
        $middleware[] = Factory::create($this->loop, $this->logger);
        if ($this->public !== null && file_exists($this->public) && is_dir($this->public)) {
            $middleware[] = new WebrootPreloadMiddleware($this->public, $this->logger);
        }
        $middleware[] = $this->handler;
        $httpServer = new ReactHttpServer($middleware);
        $httpServer->listen($socket);
    }
}
