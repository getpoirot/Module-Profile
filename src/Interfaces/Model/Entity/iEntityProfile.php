<?php
namespace Module\Profile\Interfaces\Model\Entity;

use Module\Profile\Model\Entity\Profile\GeoObject;


interface iEntityProfile
{
    /**
     * Get User Unique Identifier Belong To Profile
     *
     * @return mixed
     */
    function getUid();

    /**
     * Get Bio Text Description
     *
     * @return string|null
     */
    function getBio();

    /**
     * Get User Last Location
     *
     * @return GeoObject
     */
    function getLocation();

    /**
     * Get Gender
     *
     * @return string
     */
    function getGender();

    /**
     * Get Birthday
     *
     * @return \DateTime|null
     */
    function getBirthday();

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated();
}
