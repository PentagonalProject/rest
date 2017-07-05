<?php
namespace PentagonalProject\App\Rest\Util;

use PentagonalProject\App\Rest\Record\AppFacade;
use Psr\Container\ContainerInterface;

/**
 * Class Facade
 * @package PentagonalProject\App\Rest\Util
 */
class Facade
{
    /**
     * @param string $name
     * @param \Closure $closure
     * @param string|null $appName
     * @return ContainerInterface
     */
    public static function put(string $name, \Closure $closure, string $appName = null) : ContainerInterface
    {
        /**
         * @var ContainerInterface $container
         */
        $container = self::containerRollBackSwitch(false, $appName?: AppFacade::current()->getName());
        $container[$name] = $closure;
        return $container;
    }

    /**
     * @param string $name
     * @param \Closure $closure
     * @param string $appName
     * @return ContainerInterface
     */
    public static function putSwitch(string $name, \Closure $closure, string $appName) : ContainerInterface
    {
        /**
         * @var ContainerInterface $container
         */
        $container = self::containerRollBackSwitch(true, $appName?: AppFacade::current()->getName());
        $container[$name] = $closure;
        return $container;
    }

    /**
     * @param bool $switch
     * @param string|null $appName
     * @return ContainerInterface
     */
    private static function containerRollBackSwitch(bool $switch, string $appName = null) : ContainerInterface
    {
        $current = AppFacade::current()->getName();
        if (!$appName || $current == $appName) {
            $container = AppFacade::current()->getAccessor()->getContainer();
        } else {
            $container = AppFacade::switchTo($appName)->getAccessor()->getContainer();
            ! $switch && AppFacade::switchTo($current);
        }

        return $container;
    }

    /**
     * @param string $name
     * @param string|null $appName
     * @return mixed
     */
    public static function get(string $name, string $appName = null)
    {
        return self::containerRollBackSwitch(false, $appName?: AppFacade::current()->getName())->get($name);
    }

    /**
     * @param string $name
     * @param string $appName
     * @return mixed
     */
    public static function getSwitch(string $name, string $appName)
    {
        return self::containerRollBackSwitch(true, $appName?: AppFacade::current()->getName())->get($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments = [])
    {
        return self::get($name);
    }
}
