<?php
namespace Module\Profile\Model;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Entity\Profile\GeoObject;
use Poirot\Std\Hydrator\aHydrateEntity;


class HydrateEntityProfile
    extends aHydrateEntity
    implements iEntityProfile
{
    const FIELD_LOCATION = 'location';
    const FIELD_GENDER   = 'gender';
    const FIELD_BIRTHDAY = 'birthday';

    protected $location;
    protected $gender;
    protected $birthday;



    // Setter Options:

    /**
     * @param mixed $location
     */
    function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @param mixed $gender
     */
    function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @param mixed $birthday
     */
    function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }


    // Getter Options:

    /**
     * Get User Unique Identifier Belong To Profile
     *
     * @return mixed
     */
    function getUid()
    {
        // Has No Implementation
    }

    /**
     * Get User Last Location
     *
     * @return GeoObject
     */
    function getLocation()
    {
        // Geo Location
        if (!is_null($this->location) && !$this->location instanceof GeoObject) {
            $location = new GeoObject(GeoObject::parseWith($this->location));
            $this->location = $location;
        }

        return $this->location;
    }

    /**
     * Get Gender
     *
     * @return string
     */
    function getGender()
    {
        return ($this->gender) ? $this->gender : null;
    }

    /**
     * Get Birthday
     *
     * @return \DateTime|null
     */
    function getBirthday()
    {
        return ($this->birthday) ? new \Datetime($this->birthday) : null;
    }

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        // Has No Implementation
    }
}
