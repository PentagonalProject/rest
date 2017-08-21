<?php
namespace PentagonalProject\Tests\PhpUnit\App\Core\ResourceAdditions;

use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Abstracts\ModelValidatorAbstract;
use PentagonalProject\App\Rest\Traits\ModelValidatorTrait;

/**
 * Class ModelValidator
 * @package PentagonalProject\Tests\PhpUnit\App\Core\ResourceAdditions
 */
class ModelValidator extends ModelValidatorAbstract
{
    use ModelValidatorTrait;

    public function toCheck(): array
    {
        /**
         * @var CollectionFetch $data
         */
        $data = $this->data;
        return $data->all();
    }

    /**
     * @param \ArrayAccess $data
     *
     * @return static
     */
    public static function check(\ArrayAccess $data)
    {
        $modelValidator = new static();
        $modelValidator->data = $data;
        return $modelValidator;
    }

    public function run()
    {
        return $this;
    }
}
