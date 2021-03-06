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

use PentagonalProject\App\Rest\Abstracts\ModelValidatorAbstract;
use PentagonalProject\App\Rest\Traits\ModelValidatorTrait;
use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;

/**
 * Class RecipeValidator
 * @package PentagonalProject\Modules\Recipicious\Model\Validator
 */
class RecipeValidator extends ModelValidatorAbstract
{
    use ModelValidatorTrait;

    // Attributes
    const ATTRIBUTE_TITLE         = Recipe::COLUMN_RECIPE_TITLE;
    const ATTRIBUTE_INSTRUCTIONS  = Recipe::COLUMN_RECIPE_TITLE;
    const ATTRIBUTE_USER_ID       = Recipe::COLUMN_RECIPE_USER_ID;

    // Rules
    const RULE_NAME_MAX_LENGTH   = 60;

    /**
     * {@inheritdoc}
     */
    protected function toCheck() : array
    {
        return [
            self::ATTRIBUTE_TITLE,
            self::ATTRIBUTE_INSTRUCTIONS,
            self::ATTRIBUTE_USER_ID
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        foreach ($this->toCheck() as $toCheck) {
            $this->mustBeString($toCheck);
            $this->mustBeNotEmpty($toCheck);

            // Specific validation for name attribute
            if ($toCheck === self::ATTRIBUTE_TITLE) {
                $this->lengthMustBeLessThan(
                    $toCheck,
                    self::RULE_NAME_MAX_LENGTH
                );
            }
        }
    }
}
