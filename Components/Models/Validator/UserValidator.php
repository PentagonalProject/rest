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
    use ModelValidatorTrait;

    // Attributes
    const ATTRIBUTE_FIRST_NAME = 'first_name';
    const ATTRIBUTE_LAST_NAME  = 'last_name';
    const ATTRIBUTE_USERNAME   = 'username';
    const ATTRIBUTE_EMAIL      = 'email';
    const ATTRIBUTE_PASSWORD   = 'password';
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
     * Check whether data is already used
     *
     * @param $attribute
     * @throws ValueUsedException
     */
    private function isAlreadyUsed($attribute)
    {
        if (User::query()->where($attribute, $this->data[$attribute])->first()) {
            throw new ValueUsedException(
                sprintf(
                    "%s is already used",
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
    private function isValidUsername()
    {
        if (!preg_match(
            '/(?=^[a-z0-9_]{3,64}$)^[a-z0-9]+[_]?(?:[a-z0-9]+[_]?)?[a-z0-9]+$/',
            $this->data[self::ATTRIBUTE_USERNAME]
        )
        ) {
            throw new \InvalidArgumentException(
                "Invalid username",
                E_USER_ERROR
            );
        }
    }

    /**
     * Check whether email is valid
     *
     * @throws \InvalidArgumentException
     */
    private function isValidEmail()
    {
        $domain = new Verify();
        $email = $domain->validateEmail(
            (string) $this->data[self::ATTRIBUTE_EMAIL]
        );

        // Check whether email is valid
        if (!$email) {
            throw new \InvalidArgumentException(
                "Invalid email address",
                E_USER_ERROR
            );
        }

        $this->caseFixedEmail = $email;
    }

    protected function run()
    {
        foreach ($this->toCheck() as $toCheck => $value) {
            $this->mustBeString($toCheck);

            $this->mustBeNotEmpty($toCheck);

            $this->lengthMustBeLessThan($toCheck, $value['length']);

            // Specific validation for username or email attributes
            if ($toCheck === self::ATTRIBUTE_USERNAME || $toCheck === self::ATTRIBUTE_EMAIL) {
                $this->isAlreadyUsed($toCheck);
            }

            // Specific validation for username attribute
            if ($toCheck === self::ATTRIBUTE_USERNAME) {
                $this->isValidUsername();
            }

            // Specific validation for email attribute
            if ($toCheck === self::ATTRIBUTE_EMAIL) {
                $this->isValidEmail();
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
