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

namespace PentagonalProject\App\Rest\Record;

use Apatis\ArrayStorage\Collection;
use ArrayAccess;
use Countable;
use Exception;
use InvalidArgumentException;
use PentagonalProject\App\Rest\Abstracts\ModularAbstract;
use PentagonalProject\App\Rest\Exceptions\InvalidModularException;
use PentagonalProject\App\Rest\Exceptions\ModularNotFoundException;
use PentagonalProject\App\Rest\Util\ModularParser;
use RecursiveDirectoryIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Class ModularCollection
 * @package PentagonalProject\App\Rest\Record
 */
class ModularCollection implements Countable, ArrayAccess
{
    const TYPE_DIR = 'dir';
    const TYPE_SYMLINK = 'link';
    const TYPE_FILE = 'file';

    const CLASS_NAME_KEY = 'className';
    const FILE_PATH_KEY  = 'filePath';

    /**
     * @var RecursiveDirectoryIterator
     */
    protected $splFileInfo;

    /**
     * @var string
     */
    protected $modularDirectory;

    /**
     * @var string[]
     */
    protected $unwantedPath = [];

    /**
     * @var ModularAbstract[]|string[]
     * String if Has not Loaded and Instanceof @uses ModularAbstract if loaded
     */
    protected $validModular = [];

    /**
     * @var Exception[]
     */
    protected $invalidModular = [];

    /**
     * @var string[]
     */
    protected $loadedModular = [];

    /**
     * @var bool
     */
    protected $hasScanned = false;

    /**
     * @var array
     */
    protected $modularDefaultInfo = [
        ModularAbstract::NAME,
        ModularAbstract::VERSION,
        ModularAbstract::URI,
        ModularAbstract::AUTHOR,
        ModularAbstract::AUTHOR_URI,
        ModularAbstract::DESCRIPTION,
        ModularAbstract::CLASS_NAME,
        ModularAbstract::FILE_PATH
    ];

    /**
     * @var ModularParser
     */
    protected $modularParser;

    /**
     * ModularCollection constructor.
     * @param string $modularDirectory
     * @param ModularParser $modularParser
     */
    public function __construct(string $modularDirectory, ModularParser $modularParser)
    {
        $this->modularParser = $modularParser;
        if (!is_dir($modularDirectory) || ! is_readable($modularDirectory)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid %s Directory. %s directory not exists or has not readable by system!',
                    $this->modularParser->getName()
                ),
                E_COMPILE_ERROR
            );
        }

        $this->splFileInfo = new \SplFileInfo($modularDirectory);
        if ($this->splFileInfo->isLink()) {
            throw new RuntimeException(
                sprintf(
                    'Invalid %s Directory. %s directory could not as a symlink!',
                    $this->modularParser->getName()
                ),
                E_COMPILE_ERROR
            );
        }

        $this->modularDirectory = $this->splFileInfo->getRealPath();
    }

    /**
     * Get Path Modular Directory
     *
     * @return string
     */
    public function getModularDirectory(): string
    {
        return $this->modularDirectory;
    }

    /**
     * @return ModularAbstract[]
     */
    public function getAllValidModular() : array
    {
        return $this->validModular;
    }

    /**
     * Scan ModularAbstract Directory
     *
     * @return ModularCollection
     */
    public function scan() : ModularCollection
    {
        if ($this->hasScanned) {
            return $this;
        }

        /**
         * @var SplFileInfo $path
         */
        foreach (new RecursiveDirectoryIterator($this->getModularDirectory()) as $path) {
            $baseName = $path->getBaseName();
            // skip dotted
            if ($baseName == '.' || $baseName == '..') {
                continue;
            }

            $directory = $this->getModularDirectory() . DIRECTORY_SEPARATOR . $baseName;
            // don't allow symlink to be execute & skip if contains file
            if ($path->isLink() || ! $path->isDir()) {
                $this->unwantedPath[$baseName] = $path->getType();
                continue;
            }

            $file = $directory . DIRECTORY_SEPARATOR . $baseName .'.php';
            if (! file_exists($file)) {
                $this->invalidModular[$baseName] = new ModularNotFoundException(
                    $file,
                    sprintf(
                        '%1$s file for %2$s has not found',
                        $this->modularParser->getName(),
                        $baseName
                    )
                );

                continue;
            }

            try {
                $modular = $this
                    ->modularParser
                    ->create($file)
                    ->process();
                if (! $modular->isValid()) {
                    throw new InvalidModularException(
                        sprintf(
                            '%1$s Is not valid.',
                            $this->modularParser->getName()
                        )
                    );
                }

                $this->validModular[$this->sanitizeModularName($baseName)] = [
                    static::CLASS_NAME_KEY => $modular->getClassName(),
                    static::FILE_PATH_KEY => $modular->getFile(),
                ];
            } catch (\Exception $e) {
                $this->invalidModular[$this->sanitizeModularName($baseName)] = $e;
            }
        }

        return $this;
    }

    /**
     * Get Invalid Modular
     *
     * @return Exception[]
     */
    public function getInvalidModular() : array
    {
        return $this->invalidModular;
    }

    /**
     * Get Unwanted Path
     * contain [file|dir|link]
     *
     * @see ModularCollection::TYPE_SYMLINK
     * @see ModularCollection::TYPE_DIR
     * @see ModularCollection::TYPE_FILE
     *
     * @return string[]
     */
    public function getUnwantedPath() : array
    {
        return $this->unwantedPath;
    }

    /**
     * @see getUnwantedPath()
     *
     * @return string[]
     */
    public function getUnwantedPaths() : array
    {
        return $this->unwantedPath;
    }

    /**
     * Get Loaded Modular List base on Name
     *
     * @return string[]
     */
    public function getLoadedModularName() : array
    {
        return $this->loadedModular;
    }

    /**
     * @return ModularAbstract[]
     */
    public function getLoadedModular() : array
    {
        $modularCollections = [];
        foreach ($this->getLoadedModularName() as $value) {
            if (isset($this->validModular[$value])) {
                $modularCollections[$value] = $this->validModular[$value];
            }
        }

        return $modularCollections;
    }

    /**
     * Sanitize Modular Name
     *
     * @param string $name
     * @return string
     */
    public function sanitizeModularName(string $name) : string
    {
        return trim(strtolower($name));
    }

    /**
     * Get Modular Given By Name
     *
     * @access protected
     *
     * @param string $name
     * @return ModularAbstract
     * @throws InvalidModularException
     * @throws Exception
     */
    protected function &internalGetModular(string $name) : ModularAbstract
    {
        $modularName = $this->sanitizeModularName($name);
        if (!$modularName) {
            throw new InvalidArgumentException(
                "Please insert not an empty arguments",
                E_USER_ERROR
            );
        }

        if (!$this->exist($modularName)) {
            throw new InvalidModularException(
                sprintf(
                    '%1$s %2$s has not found',
                    $this->modularParser->getName(),
                    $name
                )
            );
        }

        if (is_array($this->validModular[$modularName])) {
            $className = empty($this->validModular[$modularName][static::CLASS_NAME_KEY])
                ? null
                : (string) $this->validModular[$modularName][static::CLASS_NAME_KEY];

            if (! $className
                || ! class_exists($this->validModular[$modularName][static::CLASS_NAME_KEY])
            ) {
                throw new InvalidModularException(
                    sprintf(
                        '%1$s %2$s has not found',
                        $this->modularParser->getName(),
                        $name
                    )
                );
            }

            $modular = new $className(
                $this->modularParser->getContainer(),
                $modularName
            );

            $this->validModular[$modularName] = $modular;
        }

        if (! $this->validModular[$modularName] instanceof ModularAbstract) {
            unset($this->validModular[$modularName]);
            $e = new InvalidModularException(
                sprintf(
                    '%1$s %2$s Is not valid.',
                    $this->modularParser->getName(),
                    $name
                )
            );

            $this->invalidModular[$modularName] = $e;
            throw $e;
        }

        return $this->validModular[$modularName];
    }

    /**
     * Get ModularAbstract's Info
     *
     * @param string $modularName
     * @return Collection
     */
    public function getModularInformation(string $modularName)
    {
        return new Collection($this->internalGetModular($modularName)->getModularInfo());
    }

    /**
     * Get All ModularAbstract Info
     *
     * @return Collection|Collection[]
     */
    public function getAllModularInfo()
    {
        $modularInfo = new Collection();
        foreach ($this->getAllValidModular() as $modularName => $modular) {
            $modularInfo[$modularName] = $this->getModularInformation($modularName);
        }

        return $modularInfo;
    }

    /**
     * Load Modular
     *
     * @param string $name
     * @return ModularAbstract
     * @throws InvalidModularException
     * @throws ModularNotFoundException
     */
    public function &load(string $name) : ModularAbstract
    {
        $modular =& $this->internalGetModular($name);
        if (!$this->hasLoaded($name)) {
            $modular->init();
            $this->loadedModular[$this->sanitizeModularName($name)] = true;
        }

        return $modular;
    }

    /**
     * Check if Modular Exists
     *
     * @param string $name
     * @return bool
     */
    public function exist(string $name)
    {
        return isset($this->validModular[$this->sanitizeModularName($name)]);
    }

    /**
     * Check If Modular Has Loaded
     *
     * @param string $name
     * @return bool
     */
    public function hasLoaded(string $name)
    {
        $modularName = $this->sanitizeModularName($name);
        return $modularName && !empty($this->loadedModular[$modularName]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->validModular);
    }

    /**
     * @param string $offset
     * @return ModularAbstract
     */
    public function offsetGet($offset)
    {
        return $this->load($offset);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    /**
     * {@inheritdoc}
     * no affected here
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
     * @param string $name
     * @return ModularAbstract
     */
    public function __get($name)
    {
        return $this->load($name);
    }
}
