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

namespace PentagonalProject\App\Rest\Util;

use Exception;
use InvalidArgumentException;
use PentagonalProject\App\Rest\Abstracts\ModularAbstract;
use PentagonalProject\App\Rest\Exceptions\EmptyFileException;
use PentagonalProject\App\Rest\Exceptions\InvalidModularException;
use PentagonalProject\App\Rest\Exceptions\InvalidPathException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SplFileInfo;

/**
 * Class ModularParser
 * @package PentagonalProject\App\Rest\Util
 */
class ModularParser
{
    /**
     * @var bool
     */
    protected $valid;

    /**
     * @var string|bool
     */
    protected $file = false;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $modularClass = ModularAbstract::class;

    /**
     * @var string
     */
    protected $name = 'Modular';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ModularParser constructor.
     * @param ContainerInterface $container
     */
    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set Container
     *
     * @param ContainerInterface $container
     * @return ModularParser
     */
    public function setContainer(ContainerInterface $container) : ModularParser
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get Container
     *
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set File
     *
     * @param string $file
     * @return ModularParser
     */
    protected function setFileToLoad(string $file) : ModularParser
    {
        if (file_exists($file)) {
            $spl = new SplFileInfo($file);
            // check if is as a symlink
            if ($spl->isLink()) {
                throw new InvalidArgumentException(
                    "Argument that given could not as a symlink path.",
                    E_WARNING
                );
            }
            // check if as a file
            if (!$spl->isFile()) {
                throw new InvalidArgumentException(
                    "Argument that given is not a file.",
                    E_WARNING
                );
            }

            if (strtolower($spl->getExtension()) !== 'php') {
                throw new InvalidArgumentException(
                    sprintf(
                        "%s file has invalid extension. Extension must be as `php`",
                        $this->getName()
                    ),
                    E_WARNING
                );
            }

            $this->file = $spl->getRealPath();
            unset($spl);
            return $this;
        }

        throw new InvalidArgumentException(
            sprintf(
                "Invalid file %s to read.",
                $this->getName()
            ),
            E_WARNING
        );
    }

    /**
     * Create Instance ModularParser
     *
     * @param string $file
     * @return ModularParser
     */
    public function create(string $file) : ModularParser
    {
        $clone = clone $this;
        return $clone->setFileToLoad($file);
    }

    /**
     * Get File Path
     *
     * @return bool|string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get Directory
     *
     * @return string
     * @throws RuntimeException
     */
    public function getDirectory() : string
    {
        if (! is_string($this->file)) {
            throw new RuntimeException(
                'Module file to parse has not determined yet.',
                E_WARNING
            );
        }

        return dirname($this->file);
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function isValid() : bool
    {
        if (! is_bool($this->valid)) {
            throw new RuntimeException(
                'Parser has not being process.',
                E_WARNING
            );
        }

        return $this->valid;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getClassName() : string
    {
        if (! is_string($this->class)) {
            throw new RuntimeException(
                'Parser has not being process.',
                E_WARNING
            );
        }

        return $this->class;
    }

    /**
     * @return ModularParser
     * @throws InvalidPathException
     * @throws Exception
     */
    public function process() : ModularParser
    {
        if (!$this->getFile()) {
            $this->valid = false;
        }

        // stop
        if (isset($this->valid)) {
            return $this;
        }

        if (preg_match('/[^a-z0-9\_]/i', pathinfo($this->file, PATHINFO_FILENAME))) {
            throw new InvalidPathException(
                $this->file,
                sprintf(
                    "Invalid base file name for %s, file name must be contain alpha numeric and underscore only",
                    basename($this->file)
                )
            );
        }

        return $this->validate();
    }

    /**
     * Validate
     *
     * @return ModularParser
     * @throws EmptyFileException
     * @throws InvalidModularException
     * @throws RuntimeException
     */
    private function validate() : ModularParser
    {
        if (!is_string($this->modularClass)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid Parent %s Class. %s extends must be as class name and string.',
                    $this->getName()
                ),
                E_WARNING
            );
        }

        $this->modularClass = rtrim($this->modularClass, '\\');
        if (!class_exists($this->modularClass)
            || strtolower($this->modularClass) != strtolower(ModularAbstract::class)
            && ! is_subclass_of($this->modularClass, ModularAbstract::class)
        ) {
            throw new RuntimeException(
                sprintf(
                    'Parent %1$s class does not extends into %2$s',
                    $this->getName(),
                    ModularAbstract::class
                )
            );
        }

        /**
         * strip white space is remove all new line and double spaces
         * and remove all comments
         * @see php_strip_whitespace()
         * just het 204b byte to get content
         */
        $content = substr(php_strip_whitespace($this->getFile()), 0, 2048);
        if (!$content) {
            throw new EmptyFileException(
                $this->getFile()
            );
        }

        if (strtolower(substr($content, 0, 5)) !== '<?php') {
            throw new InvalidModularException(
                sprintf(
                    'Invalid %s, %s does not start with open php tag.',
                    $this->getName()
                ),
                E_ERROR
            );
        }

        // remove declarations
        if (stripos($content, 'declare') !== false) {
            $content = preg_replace('`declare\s*\([^\)]+\)\s*\;\s*`smi', '$1', $content);
        }

        $namespace = '\\';
        if (preg_match('/\<\?php\s+namespace\s+(?P<namespace>[^;\{]+)/ms', $content, $nameSpaces)
            && !empty($nameSpaces['namespace'])
        ) {
            if (strtolower(trim($nameSpaces['namespace'])) == strtolower(__NAMESPACE__)) {
                throw new InvalidModularException(
                    sprintf(
                        'File %s contain name space of core.',
                        $this->getName()
                    ),
                    E_ERROR
                );
            }

            $namespace .= $nameSpaces['namespace'];
        }

        if ($namespace !== '\\' && preg_match('`[^\\\_a-z0-9]`i', $namespace, $match)) {
            throw new InvalidModularException(
                sprintf(
                    'File %s contain invalid name space.',
                    $this->getName()
                ),
                E_ERROR
            );
        }

        $modularClass = $this->modularClass;
        preg_match(
            '/use\s+
                (?:\\\{1})?(?P<extended>'.preg_quote($modularClass, '/').')
                (?:\s+as\s+(?P<alias>[a-z0-9_]+))?;+
            /smx',
            $content,
            $asAlias
        );

        $alias = isset($asAlias['alias'])
            ? $asAlias['alias']
            : null;
        if (!$alias && isset($asAlias['extended'])) {
            $asAlias['extended'] = explode('\\', $asAlias['extended']);
            $alias = end($asAlias['extended']);
        }

        // replace for unused text
        $content = preg_replace(
            [
                '`^\<\?php\s+(?:namespace\s+([^;\{])*[;\{]\s*)?`smi',
                '`(use[^;]+;\s*)*\s*(class)`smi'
            ],
            '$2',
            $content
        );

        $regexNameSpace = $alias
            ? '(?P<extends>('.preg_quote($alias, '/').'))\s*'
            : '(?P<extends>('.preg_quote("\\{$modularClass}", '/') . '|' . preg_quote($alias, '/').'))\s*';
        preg_match(
            "`class\s+(?P<class>[a-z_][a-z0-9\_]+)\s+extends\s+{$regexNameSpace}`smi",
            $content,
            $class
        );

        if (empty($class['class']) || empty($class['extends'])) {
            throw new InvalidModularException(
                sprintf(
                    'File %1$s does not contain valid class extends to `%2$s` for parser logic.',
                    $this->getName(),
                    $modularClass
                ),
                E_ERROR
            );
        }

        if (strtolower(pathinfo($this->file, PATHINFO_FILENAME)) !== strtolower($class['class'])) {
            throw new InvalidModularException(
                sprintf(
                    'File %s does not match between file name & class.',
                    $this->getName()
                ),
                E_ERROR
            );
        }

        if (! preg_match('/(public\s+)?function\s+init\([^\)]*\)\s*\{/smi', $content, $match)) {
            throw new InvalidModularException(
                sprintf(
                    'File %s does not contain method `init`.',
                    $this->getName()
                ),
                E_ERROR
            );
        }

        $class = $class['class'];
        $namespace = rtrim($namespace, '\\');
        $class = "{$namespace}\\{$class}";
        // prevent multiple include file if class has been loaded
        if (class_exists($class)) {
            throw new InvalidModularException(
                sprintf(
                    'Object class %1$s for %2$s has been loaded.',
                    $class,
                    $this->getName()
                ),
                E_ERROR
            );
        }

        // start buffer
        ob_start();

        // include once
        (function ($file) {
            /** @noinspection PhpIncludeInspection */
            require_once $file;
        })->bindTo(null)($this->file); // binding to None of $this
        if ($error = error_get_last() && !empty($error) && $error['file'] == $this->file) {
            if ($error['type'] === E_ERROR) {
                @ob_end_clean();
                throw new InvalidModularException(
                    sprintf(
                        'File %s contains fatal error.',
                        $this->getName()
                    ),
                    E_ERROR
                );
            }
        }
        // check observer
        if (ob_get_length()) {
            @ob_end_clean();
        }

        if (!class_exists($class)) {
            throw new InvalidModularException(
                sprintf(
                    'File %1$s does not contain class %2$s.',
                    $this->getName(),
                    $class
                ),
                E_ERROR
            );
        }

        if (! method_exists($class, 'init')) {
            throw new InvalidModularException(
                sprintf(
                    'File %1$s does not contain method `init`.',
                    $this->getName()
                ),
                E_ERROR
            );
        }

        $this->valid = true;
        // trim start of class name space
        $this->class = ltrim($class, '\\');
        return $this;
    }

    /**
     * Magic Method Clone
     *
     * Reset Properties if Being Clone
     */
    public function __clone()
    {
        $this->valid = null;
        $this->file = false;
        $this->class = null;
    }
}
