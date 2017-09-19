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

namespace PentagonalProject\Model\Validator;

use PentagonalProject\App\Rest\Abstracts\ModelValidatorAbstract;
use PentagonalProject\App\Rest\Record\AppFacade;
use PentagonalProject\App\Rest\Traits\ModelValidatorTrait;
use PentagonalProject\App\Rest\Util\Domain\Verify;
use PentagonalProject\Exceptions\ValueUsedException;
use PentagonalProject\Model\Database\User;

/**
 * Class UserValidator
 * @package PentagonalProject\Model\Validator
 */
class UserValidator extends ModelValidatorAbstract
{
    /**
     * Use trait for build Model Validator
     */
    use ModelValidatorTrait;

    /**
     * regex for username
     * -> length must be between or same as 3 and 64
     * -> only may contain alpha numeric and underscore
     * -> start with alpha numeric & ending with alpha numeric
     */
    const REGEX_USERNAME = '/(?=^[a-z0-9_]{3,64}$)^[a-z0-9]+[_]?(?:[a-z0-9]+[_]?)?[a-z0-9]+$/';

    // Attributes
    const ATTRIBUTE_FIRST_NAME = User::COLUMN_FIRST_NAME;
    const ATTRIBUTE_LAST_NAME  = User::COLUMN_LAST_NAME;
    const ATTRIBUTE_USERNAME   = User::COLUMN_USERNAME;
    const ATTRIBUTE_EMAIL      = User::COLUMN_EMAIL;
    const ATTRIBUTE_PASSWORD   = User::COLUMN_PASSWORD;

    // Rules
    const RULE_FIRST_NAME_MAX_LENGTH = 64;
    const RULE_LAST_NAME_MAX_LENGTH  = 64;
    const RULE_USERNAME_MAX_LENGTH   = 64;
    const RULE_EMAIL_MAX_LENGTH      = 255;
    const RULE_PASSWORD_MAX_LENGTH   = 60;

    /**
     * @var string
     */
    private $caseFixedEmail;

    /**
     * {@inheritdoc}
     */
    protected function toCheck() : array
    {
        return [
            self::ATTRIBUTE_FIRST_NAME => ['length' => self::RULE_FIRST_NAME_MAX_LENGTH],
            self::ATTRIBUTE_LAST_NAME  => ['length' => self::RULE_LAST_NAME_MAX_LENGTH],
            self::ATTRIBUTE_USERNAME   => ['length' => self::RULE_USERNAME_MAX_LENGTH],
            self::ATTRIBUTE_EMAIL      => ['length' => self::RULE_EMAIL_MAX_LENGTH],
            self::ATTRIBUTE_PASSWORD   => ['length' => self::RULE_PASSWORD_MAX_LENGTH]
        ];
    }

    /**
     * @param string $lang
     *
     * @return mixed
     */
    private function trans(string $lang)
    {
        return AppFacade::access('lang')->trans($lang);
    }

    /**
     * Check whether data is already used
     *
     * @param $attribute
     * @throws ValueUsedException
     */
    private function mustBeNotAlreadyUsed($attribute)
    {
        if (User::query()->where($attribute, $this->data[$attribute])->first()) {
            throw new ValueUsedException(
                sprintf(
                    $this->trans("%s is already used"),
                    ucwords($attribute)
                ),
                E_USER_ERROR
            );
        }
    }

    /**
     * Check whether username is valid
     *
     * @throws \InvalidArgumentException
     */
    private function trackUsername()
    {
        if (!preg_match(self::REGEX_USERNAME, $this->data[self::ATTRIBUTE_USERNAME])) {
            throw new \InvalidArgumentException(
                $this->trans("Invalid username"),
                E_USER_ERROR
            );
        }
    }

    /**
     * Check whether email is valid
     *
     * @throws \InvalidArgumentException
     */
    private function trackEmail()
    {
        $domain = new Verify();
        $email = $domain->validateEmail(
            (string) $this->data[self::ATTRIBUTE_EMAIL]
        );

        // Check whether email is valid
        if (!$email) {
            throw new \InvalidArgumentException(
                $this->trans("Invalid email address"),
                E_USER_ERROR
            );
        }

        $this->caseFixedEmail = $email;
    }

    /**
     * Run Validator
     *
     * @throws \InvalidArgumentException
     * @throws ValueUsedException
     */
    protected function run()
    {
        foreach ($this->toCheck() as $toCheck => $value) {
            $this->mustBeString($toCheck);

            $this->mustBeNotEmpty($toCheck);

            $this->lengthMustBeLessThan($toCheck, $value['length']);

            // Specific validation for username or email attributes
            if ($toCheck === self::ATTRIBUTE_USERNAME || $toCheck === self::ATTRIBUTE_EMAIL) {
                $this->mustBeNotAlreadyUsed($toCheck);
            }

            // Specific validation for username attribute
            if ($toCheck === self::ATTRIBUTE_USERNAME) {
                $this->trackUsername();
            }

            // Specific validation for email attribute
            if ($toCheck === self::ATTRIBUTE_EMAIL) {
                $this->trackEmail();
            }
        }
    }

    /**
     * Get case fixed email
     *
     * @return string
     */
    public function getCaseFixedEmail()
    {
        return $this->caseFixedEmail;
    }
}
