<?php
namespace Module\Profile\Interfaces\Model\Entity;


interface iEntityProfile
{
    /**
     * Get User Unique Identifier Belong To Profile
     *
     * @return mixed
     */
    function getUid();

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated();
}
