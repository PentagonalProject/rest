<?php
declare(strict_types=1);

namespace {

    use PentagonalProject\App\Rest\Util\Hook;
    use PentagonalProject\App\Rest\Task\Container\Override\PhpError;
    use Psr\Container\ContainerInterface;
    use Slim\Handlers\AbstractError;

    /**
     * @param ContainerInterface $container
     * @return AbstractError
     */
    return function (ContainerInterface $container) : AbstractError {
        /**
         * @var Hook $hook
         */
        $hook = $container['hook'];
        $errorPhpHandler = $hook->apply(
            'container.phpErrorHandler',
            new PhpError(
                $container->get('settings')['displayErrorDetails'],
                $container
            ),
            $container
        );

        if (! $errorPhpHandler instanceof AbstractError) {
            throw new RuntimeException(
                sprintf(
                    "Invalid Hook for Php Error Handler. Php Error Handler must be instance of %s",
                    AbstractError::class
                ),
                E_ERROR
            );
        }

        return $errorPhpHandler;
    };
}
