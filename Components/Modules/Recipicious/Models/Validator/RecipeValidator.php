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

namespace PentagonalProject\Modules\Recipicious\Model\Validator;

/**
 * Class RecipeValidator
 * @package PentagonalProject\Modules\Recipicious\Model\Validator
 */
class RecipeValidator
{
    // Attributes
    const ATTRIBUTE_NAME         = 'name';
    const ATTRIBUTE_INSTRUCTIONS = 'instructions';
    const ATTRIBUTE_USER_ID      = 'user_id';
    // Rules
    const RULE_NAME_MAX_LENGTH   = 60;

    /**
     * @var \ArrayAccess
     */
    private $data;

    private function __construct()
    {
    }

    /**
     * Check the given data
     *
     * @param \ArrayAccess $data
     */
    public static function check(\ArrayAccess $data)
    {
        $recipeValidator = new RecipeValidator();
        $recipeValidator->data = $data;
        $recipeValidator->run();
    }

    /**
     * Attributes to check
     *
     * @return array
     */
    private function toCheck()
    {
        return [
            self::ATTRIBUTE_NAME,
            self::ATTRIBUTE_INSTRUCTIONS,
            self::ATTRIBUTE_USER_ID
        ];
    }

    /**
     * Check whether data is string
     *
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    private function isString(\ArrayAccess $data, $attribute)
    {
        if (! is_string($data[$attribute])) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Recipe %s should be as a string, %s given.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    gettype($data[$attribute])
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether data is not empty
     *
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    private function isNotEmpty(\ArrayAccess $data, $attribute)
    {
        if (trim($data[$attribute]) == '') {
            throw new \InvalidArgumentException(
                sprintf(
                    "Recipe %s should not be empty.",
                    ucwords(str_replace('_', ' ', $attribute))
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether recipe name length is not more than given number of
     * characters
     *
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    private function lengthIsNotMoreThan(
        \ArrayAccess $data,
        $attribute,
        $length
    ) {
        if (strlen($data[$attribute]) > $length) {
            throw new \LengthException(
                sprintf(
                    "Recipe Name length should not more than %s characters.",
                    $length
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Run the validator
     */
    private function run()
    {
        foreach ($this->toCheck() as $toCheck) {
            $this->isString($this->data, $toCheck);

            $this->isNotEmpty($this->data, $toCheck);

            // Specific validation for name attribute
            if ($toCheck === self::ATTRIBUTE_NAME) {
                $this->lengthIsNotMoreThan(
                    $this->data,
                    $toCheck,
                    self::RULE_NAME_MAX_LENGTH
                );
            }
        }
    }
}
