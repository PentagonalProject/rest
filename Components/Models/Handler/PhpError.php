<?php
namespace PentagonalProject\Model\Handler;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\PhpError as SlimPhpError;
use Throwable;

/**
 * Class PhpError
 * @package PentagonalProject\Model\Handler
 */
class PhpError extends SlimPhpError
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PhpError constructor.
     * @param bool $displayErrorDetails
     * @param ContainerInterface|null $container
     */
    public function __construct($displayErrorDetails = false, ContainerInterface $container = null)
    {
        $this->container = $container;
        parent::__construct($displayErrorDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $error
    ) {
        if ($this->container instanceof ContainerInterface
            && $this->container->has('log')
        ) {
            /** @var Logger $log */
            $log = $this->container['log'];
            $log->error(
                $error->getMessage(),
                [
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'line' => $error->getLine()
                ]
            );
        }

        return parent::__invoke($request, $response, $error);
    }
}
