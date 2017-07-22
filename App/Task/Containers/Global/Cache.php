<?php
namespace {

    use PentagonalProject\App\Rest\Record\Configurator;
    use phpFastCache\Cache\ExtendedCacheItemPoolInterface;
    use phpFastCache\CacheManager;
    use phpFastCache\Exceptions\phpFastCacheDriverCheckException;
    use Psr\Container\ContainerInterface;

    return function (ContainerInterface $container) : ExtendedCacheItemPoolInterface {
        /**
         * @var Configurator $config
         */
        $config = $container['config'];
        // add fix to prevent trigger error
        if ($config['cache[driver]'] == 'Auto') {
            $availableDriver = CacheManager::getStaticSystemDrivers();
            foreach ($availableDriver as $driver) {
                try {
                    CacheManager::getInstance($driver, $config['cache[config]']);
                    $config['cache[driver]'] = $driver;
                    break;
                } catch (phpFastCacheDriverCheckException $e) {
                    continue;
                }
            }
        }

        $cache = CacheManager::getInstance(
            $config['cache[driver]'],
            $config['cache[config]']
        );

        $container['log']->debug(
            'Cache initiated',
            [
                'Driver' => $config['cache[driver]']
            ]
        );
        return $cache;
    };
}
