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

use PentagonalProject\App\Rest\Http\Transport;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Data
 * @package PentagonalProject\App\Rest\Util\Domain
 */
class Data
{
    const TLD_JSON_FILE = __DIR__ . '/Data/full_tld.json';

    const IANA_WHOIS_URL   = 'whois.iana.org';

    /**
     * TLD List From Iana
     */
    const IANA_TLD_ALPHA_URL = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';

    /**
     * Public suffix List
     */
    const TLD_PUBLIC_SUFFIX_URL = 'https://publicsuffix.org/list/effective_tld_names.dat';

    /**
     * @var array
     */
    protected $tldList;

    /**
     * @param StreamInterface $stream
     * @return string
     */
    public function removeCommentTextFromStream(StreamInterface $stream) : string
    {
        $string = '';
        while (!$stream->eof()) {
            $string .= $stream->getContents();
        }

        return preg_replace(
            [
                '/(\/\/|\#)[^\n]+/',
                '/(\s)+/'
            ],
            [
                '',
                '$1'
            ],
            $string
        );
    }

    /**
     * @return array
     */
    public function getTLDList()
    {
        if (!isset($this->tldList)) {
            if (file_exists(self::TLD_JSON_FILE) && is_file(self::TLD_JSON_FILE)) {
                $data = @file_get_contents(self::TLD_JSON_FILE);
                $data && $data = json_decode($data, true);
                if (is_array($data)) {
                    $this->tldList = $data;
                    return $this->tldList;
                }
            }

            $this->buildForExtension();
        }

        return $this->tldList;
    }

    /**
     * @param ResponseInterface $response
     * @param $type
     */
    private function callBackFilterArrayTLDS(ResponseInterface $response, $type)
    {
        $objectThis = $this;
        $this->tldList = [];
        array_filter(
            array_map(
                function ($data) use ($objectThis, $type) {
                    $data = trim($data);
                    if ($type == 'iana') {
                        $objectThis->tldList[idn_to_utf8($data)] = [];
                        return;
                    }

                    $data = ltrim($data, '.');
                    $countDot = substr_count($data, '.');
                    if ($countDot <> 1) {
                        return;
                    }
                    $data = idn_to_utf8($data);
                    $dataArray = explode('.', $data);
                    if (isset($objectThis->tldList[$dataArray[1]])) {
                        $objectThis->tldList[$dataArray[1]][] = $dataArray[0];
                    }
                },
                explode(
                    "\n",
                    strtolower(
                        $this->removeCommentTextFromStream($response->getBody())
                    )
                )
            )
        );
    }

    /**
     * Build For Data
     */
    public function buildForExtension()
    {
        $objectThis = $this;
        Transport::create()
            ->getAsync(self::IANA_TLD_ALPHA_URL)
            ->then(function (ResponseInterface $response) use ($objectThis) {
                $objectThis->callBackFilterArrayTLDS($response, 'iana');
                Transport::create()
                    ->getAsync(self::TLD_PUBLIC_SUFFIX_URL)
                    ->then(function (ResponseInterface $response) use ($objectThis) {
                        $objectThis->callBackFilterArrayTLDS($response, 'sub');
                        $dir = dirname(self::TLD_JSON_FILE);
                        $jsonExists = is_file(self::TLD_JSON_FILE);
                        if (!$jsonExists && !file_exists($dir)) {
                            if (!@mkdir($dir, 755, true)) {
                                return;
                            }
                        }
                        if ($jsonExists && is_writeable(self::TLD_JSON_FILE)
                            || ! $jsonExists && is_dir($dir) && is_writeable($dir)
                        ) {
                            @file_put_contents(
                                self::TLD_JSON_FILE,
                                json_encode($objectThis->tldList, JSON_PRETTY_PRINT)
                            );
                        }
                    })->wait();
            })->wait();
    }
}
