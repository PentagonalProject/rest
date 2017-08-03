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

namespace PentagonalProject\App\Rest\Util\Domain;

use DomainException;
use HttpUrlException;
use PentagonalProject\App\Rest\Util\Sanitizer;

/**
 * Class WhoIs
 * @package PentagonalProject\App\Rest\Util\Domain
 */
class WhoIs
{
    /**
     * @var array
     */
    protected $cachedWhoIsServers = [];

    /**
     * @var array
     */
    protected $cachedWhoIsDomain = [];

    /**
     * @var Verify
     */
    protected $verify;

    /**
     * WhoIs constructor.
     */
    public function __construct()
    {
        $this->verify = new Verify();
    }

    /**
     * @param string $domain
     * @param string $server
     * @return string
     */
    protected function runSocketConnection($domain, $server)
    {
        $serverDetails = explode(':', $server);
        $socket = @fsockopen($serverDetails[0], abs($serverDetails[1]), $errNumber, $errString);
        if (!$socket) {
            usleep(600);
            $socket = @fsockopen($serverDetails[0], $serverDetails[1], $errNumber, $errString);
        }
        if (!$socket) {
            throw new \UnexpectedValueException(
                $errString,
                $errNumber
            );
        }
        if (!fputs($socket, "{$domain}\r\n")) {
            @fclose($socket);
            throw new \UnexpectedValueException(
                'Can not put data into whois',
                E_ERROR
            );
        }
        $data = '';
        while (!feof($socket)) {
            $data .= fgets($socket);
        }
        @fclose($socket);
        unset($socket);

        return $data;
    }

    private function cleanData($data) : string
    {
        $data = trim($data);
        $data = preg_replace(
            '/(\>\>\>|URL\s+of\s+the\s+ICANN\s+WHOIS).*/is',
            '',
            $data
        );
        if (strpos($data, '#') !== false || strpos($data, '%') !== false) {
            $data = implode(
                "\n",
                array_filter(
                    explode("\n", $data),
                    function ($data) {
                        return !(
                            strpos(trim($data), '#') === 0
                            || strpos(trim($data), '%') === 0
                        );
                    }
                )
            );
        }

        return trim($data);
    }

    /**
     * @param string $domainName
     * @param string $data
     * @param string $oldServer
     * @return array
     */
    protected function getForWhoIsServerAlternative(
        string $domainName,
        string $data,
        string $oldServer
    ) : array {
        try {
            $data = $this->cleanData($data);
            preg_match('/Whois\s+Server:\s*(?P<server>[^\s]+)/i', $data, $match);
            if (empty($match['server'])) {
                return [
                    $oldServer => $data
                ];
            }

            $data2 = $this->runSocketConnection($domainName, "{$match['server']}:43");
            if (!empty($data2)) {
                $array = explode('.', $domainName);
                $this->cachedWhoIsServers[end($array)] = $match['server'];
                $data2 = $this->cleanData($data2);
                return [
                    $oldServer => $data,
                    $match['server'] => $data2
                ];
            }
        } catch (\Exception $e) {
        }

        return [
            $oldServer => $data
        ];
    }

    /**
     * @param string $domain
     * @return mixed
     * @throws HttpUrlException
     * @throws DomainException
     */
    public function getWhoIsServer($domain)
    {
        if (!$this->verify->isTopDomain($domain)) {
            throw new DomainException(
                "Domain is not valid!",
                E_ERROR
            );
        }

        $array = explode('.', $domain);
        if (! isset($this->cachedWhoIsServers[end($array)])) {
            $this->cachedWhoIsServers[end($array)] = false;
            $body = $this->runSocketConnection($domain, Data::IANA_WHOIS_URL . ":43");
            preg_match('/whois:\s*(?P<server>[^\n]+)/i', $body, $match);
            if (!empty($match['server']) && ($server = trim($match['server']) != '')) {
                $this->cachedWhoIsServers[end($array)] = $match['server'];
                return $match['server'];
            }
        }

        if ($this->cachedWhoIsServers[end($array)]) {
            return $this->cachedWhoIsServers[end($array)];
        }

        throw new \UnexpectedValueException(
            'Whois check failed ! Whois server not found.',
            E_ERROR
        );
    }

    /**
     * @param string $domainName
     * @return array
     */
    public function getWhoIsWithArrayDetail(string $domainName) : array
    {
        $whoIs = $this->getWhoIs($domainName);
        foreach ($whoIs as $key => $value) {
            $whoIs[$key] = $this->parseDataDetail($value);
        }

        return $whoIs;
    }

    /**
     * @param string $string
     * @return array
     */
    private function parseDataDetail(string $string) : array
    {
        $string = explode("\n", $string);
        $data = [];
        foreach ($string as $value) {
            if (strpos($value, ':') !== false) {
                $value = explode(':', $value);
                $key = $this->convertNameToUpperCaseTrimmed((string) array_shift($value));
                $data[$key][] = trim(implode(':', $value));
            }
        }

        return $data;
    }

    /**
     * @param string $name
     * @return string
     */
    private function convertNameToUpperCaseTrimmed(string $name) : string
    {
        $string = ucwords(
            trim(trim($name), '.')
        );
        return preg_replace('/(\s)+/', '$1', $string);
    }
    /**
     * @param string $domainName
     * @return array
     */
    public function getWhoIs(string $domainName) : array
    {
        $whoIsServer = $this->getWhoIsServer($domainName);
        return $this->getForWhoIsServerAlternative(
            $domainName,
            $this->runSocketConnection($domainName, "{$whoIsServer}:43"),
            $whoIsServer
        );
    }
}
