<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author pentagonal <org@pentagonal.org>
 */

declare(strict_types=1);

namespace PentagonalProject\App\Rest\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Transport
 * @package PentagonalProject\App\Rest\Http
 *
 * Extended Guzzle HTTP Client Remote Transport
 *
 * @method ResponseInterface get(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface head(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface put(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface patch(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface delete(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface options(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface link(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface unLink(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface copy(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface purge(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface view(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface propView(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface lock(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface unLock(string|UriInterface $uri, array $options = [])
 *
 * @method PromiseInterface getAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface headAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface putAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface postAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface patchAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface deleteAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface optionsAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface linkAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface unLinkAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface copyAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface purgeAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface viewAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface propViewAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface lockAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface unLockAsync(string|UriInterface $uri, array $options = [])
 *
 * @method ResponseInterface request($method, $uri = '', array $options = [])
 * @method mixed             getConfig($option = null)
 * @method ResponseInterface send(RequestInterface $request, array $options = [])
 * @method ResponseInterface sendAsync(RequestInterface $request, array $options = [])
 */
class Transport
{
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0';

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * Transport constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->guzzleClient = new Client($config);
    }

    /**
     * Generate Dummy Browser
     *
     * @return string
     */
    public function generateDummyBrowser() : string
    {
        static $ua;
        if (isset($ua)) {
            return $ua;
        }

        $year  = abs(@date('Y'));
        if ($year <= 2017) {
            return $ua = self::DEFAULT_USER_AGENT;
        }

        $user_agent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:[version].0) Gecko/20100101 Firefox/[version].0';
        $month      = abs(@date('m'));
        $version    = 51;
        $currentYear    = ($year-2017);
        $currentVersion = is_int($month/2) ? $month/2 : abs($month/2 + 0.5);
        $version   += $currentYear + $currentVersion;
        return $ua = str_replace('[version]', $version, $user_agent);
    }

    /**
     * Retrieve Transport with header like a browser uses
     *
     * @return Transport
     */
    public function withBrowser() : Transport
    {
        $object = clone $this;
        $config = $object->guzzleClient->getConfig();
        $config['headers'] = !empty($config['headers']) && is_array($config['headers'])
            ? $config['headers']
            : [];

        // browser manipulation
        $config['headers'] =  array_merge(
            $config['headers'],
            [
                'User-Agent'      => $this->generateDummyBrowser(), # Get Dummy Browser
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Connection'      => 'keep-alive',
                'Pragma'          => 'no-cache',
                'Cache-Control'   => 'no-cache',
                'Upgrade-Insecure-Requests' => '1',
            ]
        );

        return $object->withClient(new Client($config));
    }

    /**
     * Retrieve Transport with Default Header set
     *
     * @return Transport
     */
    public function withoutBrowser() : Transport
    {
        $object = clone $this;
        $config = $object->guzzleClient->getConfig();
        $config['headers'] = !empty($config['headers']) && is_array($config['headers'])
            ? $config['headers']
            : [];
        if (!empty($config['headers'])) {
            foreach ($config['headers'] as $key => $value) {
                if (in_array(
                    strtolower($key),
                    [
                        'accept', 'accept-encoding', 'accept-language',
                        'connection', 'pragma', 'upgrade-insecure-requests'
                    ]
                )) {
                    unset($config['headers'][$key]);
                }
            }

            return $object->withClient(new Client($config));
        }

        return $object;
    }

    /**
     * With Client
     *
     * @param Client $client
     * @return Transport
     */
    public function withClient(Client $client) : Transport
    {
        $clone = clone $this;
        $clone->guzzleClient = $client;
        return $clone;
    }

    /**
     * @param CookieJar|null $cookieJar
     * @return Transport
     */
    public function withCookieJar(CookieJar $cookieJar = null) : Transport
    {
        $config = $this->guzzleClient->getConfig();
        $config['cookies'] = $cookieJar?: new CookieJar();

        return $this->withClient(new Client($config));
    }

    /**
     * Clone Without Cookies
     *
     * @return Transport
     */
    public function withoutCookie() : Transport
    {
        $config = $this->guzzleClient->getConfig();
        unset($config['cookies']);

        return $this->withClient(new Client($config));
    }

    /**
     * Create new Instance
     *
     * @param array $config
     * @return Transport
     */
    public static function create(array $config = []) : Transport
    {
        return new static($config);
    }

    /**
     * Magic Method
     *
     * @param string $name
     * @param array $arguments
     * @return PromiseInterface|ResponseInterface
     */
    public function __call(string $name, array $arguments)
    {
        // fix method
        $newName = substr($name, -5);
        if ($newName != 'Async' && strtolower($newName) == 'async') {
            $name = substr($name, 0, -5) . 'Async';
        }

        return call_user_func_array(
            [$this->guzzleClient, $name],
            $arguments
        );
    }
}
