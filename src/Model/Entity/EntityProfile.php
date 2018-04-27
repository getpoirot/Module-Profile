<?php
namespace Module\Profile\Model\Entity;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Entity\Profile\GeoObject;
use Poirot\Std\Struct\DataOptionsOpen;


class EntityProfile
    extends DataOptionsOpen
    implements iEntityProfile
{
    const GENDER_MALE     = 'male';
    const GENDER_FEMALE   = 'female';

    const PRIVACY_PRIVATE = 'private';
    const PRIVACY_PUBLIC  = 'public';
    const PRIVACY_FRIENDS = 'friends';
    const PRIVACY_FOFS    = 'fofs';   // friend of friends


    protected $uid;
    protected $displayName;
    protected $bio;
    /** @var GeoObject */
    protected $location;
    /** @var string */
    protected $gender;
    protected $privacyStatus = self::PRIVACY_PUBLIC;
    /** @var \DateTime */
    protected $birthday;
    /** @var \DateTime */
    protected $datetimeCreated;


    /**
     * Set Profile Owner UID
     *
     * @param mixed $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get User Unique Identifier Belong To Profile
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->uid;
    }

    /**
     * Set Display Name
     *
     * @param string $name
     *
     * @return $this
     */
    function setDisplayName($name)
    {
        $this->displayName = ($name !== null) ? (string) $name : $name;
        return $this;
    }

    /**
     * Get Display Name
     *
     * @return string
     */
    function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set Bio Description
     *
     * @param string|null $description
     *
     * @return $this
     */
    function setBio($description)
    {
        $this->bio = $description;
        return $this;
    }

    /**
     * Get Bio Text Description
     *
     * @return string|null
     */
    function getBio()
    {
        return ( ($this->bio !== null) ? (string) $this->bio : null );
    }

    /**
     * Set User Last Location
     *
     * @param GeoObject $location
     *
     * @return $this
     */
    function setLocation($location)
    {
        if ( !($location === null || $location instanceof GeoObject) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of GeoObject or null; given: (%s).'
                , \Poirot\Std\flatten($location)
            ));


        $this->location = $location;
        return $this;
    }

    /**
     * Get User Last Location
     *
     * @return GeoObject
     */
    function getLocation()
    {
        return $this->location;
    }

    /**
     * Set Gender
     *
     * @param string $gender
     *
     * @return $this
     */
    function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get Gender
     *
     * @return string|null
     */
    function getGender()
    {
        return ( (null !== $this->gender) ? (string) $this->gender : null );
    }

    /**
     * Get Privacy Status
     *
     * @return string
     */
    function getPrivacyStatus()
    {
        return $this->privacyStatus;
    }

    /**
     * Set Privacy Status
     *
     * @param string $privacyStatus
     *
     * @return $this
     */
    function setPrivacyStatus($privacyStatus)
    {
        $this->privacyStatus = (string) $privacyStatus;
        return $this;
    }

    /**
     * Set Birthday
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setBirthday($dateTime)
    {
        if ( !($dateTime === null || $dateTime instanceof \DateTime) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of \Datetime or null; given: (%s).'
                , \Poirot\Std\flatten($dateTime)
            ));


        $this->birthday = $dateTime;
        return $this;
    }

    /**
     * Get Birthday
     *
     * @return \DateTime|null
     */
    function getBirthday()
    {
        return $this->birthday;
    }


    /**
     * Set Created Timestamp
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setDateTimeCreated($dateTime)
    {
        if ( !($dateTime === null || $dateTime instanceof \DateTime) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of \Datetime or null; given: (%s).'
                , \Poirot\Std\flatten($dateTime)
            ));


        $this->datetimeCreated = $dateTime;
        return $this;
    }

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        if (! $this->datetimeCreated )
            $this->setDateTimeCreated( new \DateTime );

        return $this->datetimeCreated;
    }
}
