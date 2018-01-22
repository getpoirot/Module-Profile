<?php
namespace Module\Profile\Events;

use Module\Profile\Model\Entity\EntityAvatar;
use Poirot\Events\Event;
use Poirot\Events\EventHeap;
use Module\Profile\Model\Entity\EntityProfile;


class EventsHeapOfProfile
    extends EventHeap
{
    const AVATAR_UPLOADED = 'avatar.new';
    const RETRIEVE_PROFILE_RESULT = 'retrieve.profile.result';


    /**
     * Initialize
     *
     */
    function __init()
    {
        $this->collector = new DataCollector;

        // Avatar:
        $this->bind( new Event(self::AVATAR_UPLOADED, new Event\BuildEvent([
            'collector' => new DTofUploadAvatar ])) );


        // attach default event names:
        $this->bind( new Event(self::RETRIEVE_PROFILE_RESULT) );
    }


    /**
     * @override ide auto info
     * @inheritdoc
     *
     * @return DataCollector
     */
    function collector($options = null)
    {
        return parent::collector($options);
    }
}


class DTofUploadAvatar
    extends Event\DataCollector
{
    /** @var EntityAvatar */
    protected $avatarEntity;


    /**
     * @return EntityAvatar
     */
    function getAvatarEntity()
    {
        return $this->avatarEntity;
    }

    /**
     * @param mixed $avatarEntity
     */
    function setAvatarEntity($avatarEntity)
    {
        $this->avatarEntity = $avatarEntity;
    }
}


class DataCollector
    extends \Poirot\Events\Event\DataCollector
{
    protected $visitor;
    /** @var EntityProfile */
    protected $entityProfile;
    protected $result;


    /**
     * Who Request The Page (user session)
     * @param mixed|null $visitor User identifier
     */
    function setVisitor($visitor)
    {
        $this->visitor = $visitor;
    }

    function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param EntityProfile | null $profile
     */

    function setEntityProfile($profile)
    {
        $this->entityProfile = $profile;
    }

    function getEntityProfile()
    {
        return $this->entityProfile;
    }


    // .. retrieve.content.result

    function getResult()
    {
        return $this->result;
    }

    function setResult($result)
    {
        $this->result = $result;
    }
}
