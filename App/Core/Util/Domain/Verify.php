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

/**
 * Class Verify
 * @package PentagonalProject\App\Rest\Util\Domain
 */
class Verify
{
    const IPV4_REGEX = '~^
        # start with 0. to 255.
        (?:0|[1-9][0-9]?|1[0-9]{0,2}|2[0-5]{0,2})
        # start with 0. to 255. 3 times
        (?:\.(?:0|[1-9][0-9]?|1[0-9]{0,2}|2[0-5]{0,2})){3}
    $~x';

    const IPV4_LOCAL_REGEX = '~^
        (?:
            (?:
                1?0 | # start with 0. or 10.
                127  # start with 127.
            )\.(?:0|1[0-9]{0,2}|2[0-5]{0,2}) # next 0 to 255
            | 192\.168
            | 172\.16
        )
        # next 0. to 255. twice
        (?:
            \.(?:0|1[0-9]{0,2}|2[0-5]{0,2})
        ){2}
    $~x';

    /**
     * @var array
     */
    protected static $extensionList = [];

    /**
     * @var string
     */
    protected $urlTLDs = 'https://publicsuffix.org/list/effective_tld_names.dat';

    /**
     * Regex Global Domain
     */
    const REGEX_GLOBAL = '/[^a-z0-9\-\P{Latin}\P{Hebrew}\P{Greek}\P{Cyrillic}
        \P{Han}\P{Arabic}\P{Gujarati}\P{Armenian}\P{Hiragana}\P{Thai}]/x';

    /**
     * @var array
     */
    protected $regexExtension = [
        "com" => self::REGEX_GLOBAL,
        "net" => self::REGEX_GLOBAL
    ];

    /**
     * @var array
     * just add common to prevent spam
     */
    protected $commonEmailProvider = [
        'gmail', 'hotmail', 'outlook', 'yahoo',
        'mail', 'gmx', 'inbox', 'rocketmail',
        'hushmail', 'null', 'hush',
        'hackrmail', 'yandex'
    ];

    const SELECTOR_FULL_NAME       = 'domain_name';
    const SELECTOR_DOMAIN_NAME     = 'domain_name_base';
    const SELECTOR_EXTENSION_NAME  = 'domain_extension';
    const SELECTOR_SUB_DOMAIN_NAME = 'sub_domain';

    /**
     * Domain constructor.
     */
    public function __construct()
    {
        if (empty(self::$extensionList)) {
            $data = new Data();
            self::$extensionList = $data->getTLDList();
        }
    }

    /**
     * @return array
     */
    public function getExtensionList() : array
    {
        return self::$extensionList;
    }

    /**
     * @param string $string
     * @return bool|string
     */
    public function getExtensionIDN(string $string)
    {
        if (!is_string($string) || strlen(trim($string)) < 2) {
            return false;
        }

        $string  = strtolower(trim($string));
        if (function_exists('idn_to_ascii')) {
            $string = idn_to_ascii($string);
            return isset(self::$extensionList[$string])
                ? idn_to_utf8($string)
                : false;
        } elseif (substr($string, 3) == 'xn-') {
            return false;
        }

        if (isset(self::$extensionList[$string])) {
            return $string;
        }

        return false;
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isExtensionExist(string $string) : bool
    {
        return $this->getExtensionIDN($string) !== false;
    }

    /**
     * @param string $domainName
     * @return array|bool
     */
    public function validateDomain(string $domainName)
    {
        if (! is_string($domainName)
            || trim($domainName) === ''
            || strlen($domainName) > 255
            || ! strpos($domainName, '.')
            || preg_match('/(?:^[\-\.])|[~!@#$%^&*()+`=\\|\'{}\[\\];":,\/<>?\s]|[\-]\.|\.\.|(?:[-.]$)/', $domainName)
        ) {
            return false;
        }

        $domainName = strtolower($domainName);
        $result = [
            self::SELECTOR_FULL_NAME  => $domainName,
            self::SELECTOR_SUB_DOMAIN_NAME => null,
            self::SELECTOR_DOMAIN_NAME => null,
            self::SELECTOR_EXTENSION_NAME => null,
        ];

        $arrayDomain = explode('.', $domainName);
        $arrayDomainLength = count($arrayDomain);
        $result[self::SELECTOR_EXTENSION_NAME] = array_pop($arrayDomain);
        $result[self::SELECTOR_DOMAIN_NAME]    = array_pop($arrayDomain);
        $extension = function_exists('idn_to_ascii')
            ? idn_to_ascii($result[self::SELECTOR_EXTENSION_NAME])
            : $result[self::SELECTOR_EXTENSION_NAME];

        if (!isset(self::$extensionList[$extension])
            || $result[self::SELECTOR_DOMAIN_NAME] > 63
            /* just make sure example.(com|org) not used */
            /* || result[1] === 'example' && ['com', 'org'].indexOf(result.extension_domain) > -1 */
        ) {
            return false;
        }

        if (isset($this->regexExtension[$extension])
            && @preg_match(
                preg_replace('/\s*/', '', $this->regexExtension[$extension]),
                $result[self::SELECTOR_DOMAIN_NAME],
                $match,
                PREG_NO_ERROR
            )
        ) {
            return false;
        } elseif (!preg_match('/^[a-z0-9]+(?:(?:[a-z0-9-]+)?[a-z0-9]$)?/', $result[self::SELECTOR_DOMAIN_NAME])) {
            return false;
        }

        if ($arrayDomainLength > 2) {
            $result[self::SELECTOR_SUB_DOMAIN_NAME] = implode('.', $arrayDomain);
        }
        $result[self::SELECTOR_FULL_NAME] = $result[self::SELECTOR_SUB_DOMAIN_NAME]
            . $result[self::SELECTOR_DOMAIN_NAME];
        return $result;
    }

    /**
     * @param string $domainName
     * @return bool
     */
    public function isDomain(string $domainName) : bool
    {
        return is_array($this->validateDomain($domainName));
    }

    /**
     * @param string $domainName
     * @return bool|string
     */
    public function validateTopDomain(string $domainName)
    {
        $domain = $this->validateDomain($domainName);
        if (is_array($domain) && (
                empty($domain[self::SELECTOR_SUB_DOMAIN_NAME])
                || strpos($domain[self::SELECTOR_SUB_DOMAIN_NAME], '.') === false
                && in_array(
                    $domain[self::SELECTOR_DOMAIN_NAME],
                    self::$extensionList[$domain[self::SELECTOR_EXTENSION_NAME]]
                )
            )
        ) {
            return strtolower($domainName);
        }

        return false;
    }

    /**
     * @param string $domainName
     * @return bool
     */
    public function isTopDomain(string $domainName) : bool
    {
        return is_string($this->validateTopDomain($domainName));
    }

    /**
     * @param string $email
     * @return bool|string
     */
    public function validateEmail(string $email)
    {
        if (!is_string($email) || strlen(trim($email)) < 6 || substr_count($email, '@') <> 1
            || stripos($email, '.') === false
        ) {
            return false;
        }

        $email = trim(strtolower($email));
        if (preg_match('/(?:^@)|(?:@$)/', $email, $match)) {
            return false;
        }

        $emailArray = explode('@', $email);
        if (count($emailArray) <> 2 || (!$domainArray = $this->validateDomain($emailArray[1]))) {
            return false;
        }
        // sanity on global domains
        if (in_array($domainArray[self::SELECTOR_EXTENSION_NAME], ['de', 'ru', 'co', 'net', 'com'])) {
            if (! empty($domainArray[self::SELECTOR_SUB_DOMAIN_NAME])) {
                return in_array($domainArray[self::SELECTOR_DOMAIN_NAME], $this->commonEmailProvider);
            }
        }

        if (strlen($emailArray[0]) > 254
            /**
             * for standard usage email address only contains:
             * alphabetical & underscore (_) dash (-) and dotted (.)
             */
            || preg_match('/[^a-z0-9_\-.]/', $emailArray[0])
            || preg_match('/(?:\.\.)|(?:^[-_])|(?:[-_]$)/', $emailArray[0])
            /**
             * Could not contain non alphabetical or numeric on start or end of email address
             */
            || ! preg_match('/^[a-z0-9]/', $emailArray[0])
            || ! preg_match('/[a-z0-9]$/', $emailArray[0])
        ) {
            return false;
        }

        return "{$emailArray[0]}@{$domainArray[self::SELECTOR_FULL_NAME]}"
            . ".{$domainArray[self::SELECTOR_EXTENSION_NAME]}";
    }

    /**
     * @param string $email
     * @return bool
     */
    public function isEmail(string $email) : bool
    {
        return is_string($this->validateEmail($email));
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function isIPv4(string $ip) : bool
    {
        if (strlen($ip) > 15) {
            return false;
        }
        return (bool) preg_match(
            self::IPV4_REGEX,
            $ip
        );
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function isLocalIPv4(string $ip) : bool
    {
        if (strlen($ip) > 15) {
            return false;
        }

        return (bool) preg_match(
            self::IPV4_LOCAL_REGEX,
            $ip
        );
    }

    /**
     * @param string $name
     * @return bool|string
     */
    public function validateASN(string $name)
    {
        if (preg_match('/^[A-Z]{2,4}[0-9]+$/i', $name)) {
            return strtoupper($name);
        }

        return false;
    }
}
