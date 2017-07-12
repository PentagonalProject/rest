<?php
namespace {

    use PentagonalProject\App\Rest\Record\ModularCollection;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;

    if (!isset($this) || ! $this instanceof App) {
        return;
    }

    // register Middleware

    /**
     * Middle ware to register Module persistent
     */
    $this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
        /**
         * @var ModularCollection $Modular
         */
        $Modular = $this['module'];
        /**
         * @var string[] list Module To Load
         */
        $listModuleLoads = [
            'recipicious',
        ];

        // doing load
        array_map(function ($moduleName) use ($Modular) {
            $Modular->exist($moduleName) && $Modular->load($moduleName);
        }, $listModuleLoads);

        return $next($request, $response);
    });
}
