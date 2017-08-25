<?php
namespace Module\Profile\Model;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Poirot\Std\aValidator;
use Poirot\Std\Exceptions\exUnexpectedValue;


class ProfileValidate
    extends aValidator
{
    /** @var iEntityProfile */
    protected $entity;


    /**
     * Construct
     **
     * @param iEntityProfile $entity
     */
    function __construct(iEntityProfile $entity = null)
    {
        $this->entity = $entity;
    }


    /**
     * Do Assertion Validate and Return An Array Of Errors
     *
     * @return exUnexpectedValue[]
     */
    function doAssertValidate()
    {
        $exceptions = [];

        // TODO do validation

        return $exceptions;
    }
}
