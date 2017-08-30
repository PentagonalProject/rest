# CONTRIBUTION

This repository being managed by **Pentagonal Team**, that means it was as a repository that fully managed and must be follow the Rules & Guidelines and all of code that requested being reviewed before merge.

This repository & libraries being free to use without warranty, but must be following **License & Basic Usage**.

To contribute you could to clone or fork repository and request to make pull request, merge or open issues to support of development process.

Note :

```
We are 100% humans that have much & many limit, we may have many of mistake that reviewing, merging, writing or anything.
So, if we have some (or many) mistake about merging request or misused of intellectual property about the code,
please consider to contact us on :

email : dev@pentagonal.org

```

# CODE OF CONDUCT

```
- The repository have a standard code & structured that being created by Pentagonal Team.
- All of code on scripts must be following standard & guidelines that must be :
    Readable, Documented, Trusted, Safe & Responsible about code that written or push to the repository.
- The code that implements to new files / contributions must be contains of self written or not used from other unpermitted licensed or another unpermitted intellectual property for the code ethic purpose.
```

# GUIDELINES

Below is list of **HOW TO** contribute about writing & implementing the code before make (a) pull request(s)


## PULL REQUESTS

Please follow the guide from this article how to make pull requests [https://opensource.guide/how-to-contribute/](https://opensource.guide/how-to-contribute/)

## WRITING CODE

The writing code procedure must be readable and reusable.

1. Should using `PSR2 Coding Standards`
<br/>see : [http://www.php-fig.org/psr/psr-2/](http://www.php-fig.org/psr/psr-2/) for best practice.
2. Before push must be contains valid Code Sniffer Lint
<br/>see : [https://github.com/squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
<br />Configuration file is on [phpcs.xml](phpcs.xml)
```bash
vendor/bin/phpcs
```
3. Writing `Database Query Syntax` must be compatible between `MySQL` & `PostgreSQL`.
<br/>
If there are additional database SQL Structure to add on core / SQL file must be add also for PostgreSQL & MySQL Query Properties.

- [Database.sql](Database.sql) for MySQL
- [Database.pg.sql](Database.pg.sql) for PostgreSQL.
  
4. `Well Documented` or written in valuable code:
- Writing Function & Method should use `PhpDoc` as well
<br/>see: [https://www.phpdoc.org/](https://www.phpdoc.org/)
- Must be contain `@return` tag if there are return value (optional if method returning void)
- When using inheritance method and contains additional logic or different return value, must be ad additional comments
if does not contains additional logic or just override and add additional sanitation it could use `{@inheritdoc}` comments.
- Use maximum `120 Characters` each line `even on comments section`.
- Use `CamelCase` , do not use underScore on each start of methods and properties to prevent conflict of global & readable context.  
- Must be contain method & property visibility declarations on writing `OOP - Object Oriented Programming`
<br/>example:
```php
    ...
    /**
     * this is property of comment that contain data type string
     * with protected visbility
     * @var string of property declaration target for
     */
    protected $property;
    
    /**
     * This is comments of Method
     * with public visibility declaration 
     * @return ReturnType Return Type
     */
    public function method() : ReturnType
    {
        return (ReturnType) ...;
    }
    ...
```
- Should add `Return Type` if there methods have a static / permanent result return type that following Php7 features on `OOP - Object Oriented Programming` *(see example above)*.
- Should use strict style and must be implements `declare(stric_types=1)` or use strict type `declare` directive to use as well coding.
<br/>example:
```php
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
 * @author YouName <email@example.com>
 *
 * Stop Here if there is not exists additional info & `Above License Must Be` put on each file.
 * Additional Notes Here that contains much of additional information.
 * eg: 
 * The code taken from repository from bla bla ... that contains 
 * license GPL3 or later that following the repository or code from bla bla
 * 
 * The Revision is bla bla bla ....
 */

declare(strict_types=1);

namespace MyNameSpace;

/**
 * class MyClass
 * @subpackage MyNameSpace
 * This is additional Comments
 * Below Comments example for magic method
 *
 * @method string method(mixed $param)
 * @property string $property
 */
class CamelCaseClass extends OtherClass
{
    /**
     * Information about method
     * 
     * @access internal for internal use only comment information 
     * @return void
     */
    protected function camelCaseMethod()
    {
        // process
    }

    /**
     * {@inheritdoc}
     * Additional information about override method 
     */
    public function inheritanceMethod()
    {
        // ... do
    }

    /**
     * Magic Method for Object method
     * @return mixed|void 
     */
    public function __call(string $name, array $arguments)
    {
        // process here
    }

    /**
     * Magic Method for Object Properties 
     */
    public function __get($name)
    {
        // process here
    }
}

``` 
- Add License Comments on first start php file *(see above)*.
- Add Comments for `Method`, `Property` etc.
- Use no `@noinspection` for undeclared notice `IDE` on
- The code that written into file(s) must be follow of **License** & **Intellectual Property** by respective owner.
    - The file(s) must be not contains full copy of code that not being yours.
    - Partial code can be implemented but must be permitted by respective owner and show not contains full fill of copy partial method / logic code.  
    - When the code taken from partial or `full copy of yours`, must be adds following license (type) as detail of code uses and note about the code from.

And other things that can be accounted for and make better in contributing process.

All of code must be tested (not writing only) by you that make sure your code could be run properly.

## CODE TEST / UNIT TESTING

We greatly appreciate and expect contributions to the unit testing section using standard and coverage literature.

To run unit testing could use code below to know about detail coverage 
<br />Configuration file is on [phpunit.xml.dist](phpunit.xml.dist)

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-text
```

## OPENING ISSUES

Please open issue(s) with right label and there are way to fix for bug please consider to create work flow procedure for the issues & details if exists.

## LICENSING

The code must be contains `MIT` License.
Please read the License on : [LICENSE](LICENSE)

## NOTE

Everything contained in this repository may change at any time, and or subject to alterations of content and policies without prior notice.
