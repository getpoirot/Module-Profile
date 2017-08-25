<?php
namespace Module\Profile\Model\Entity;

use Module\Profile\Model\Entity\Avatars\aMediaObject;
use Poirot\Std\Struct\aDataOptions;


class EntityAvatar
    extends aDataOptions
{
    protected $uid;
    /** @var aMediaObject[]  */
    protected $medias = [];
    /** @var \DateTime */
    protected $datetimeCreated;


    /**
     * Set Avatar Owner UID
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
     * Get User Unique Identifier Belong To Avatar
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->uid;
    }

    /**
     * Set Avatars Attached Medias
     *
     * @param []EntityPostMediaObject $medias
     *
     * @return $this
     */
    function setMedias(array $medias)
    {
        $this->medias = array();

        foreach ($medias as $m)
            $this->addMedia($m);

        return $this;
    }

    /**
     * Get Attached Avatars
     *
     * @return array aMediaObject[]
     */
    function getMedias()
    {
        return $this->medias;
    }

    /**
     * Attach Media To Avatars
     *
     * @param aMediaObject $media
     *
     * @return $this
     */
    function addMedia(aMediaObject $media)
    {
        $this->medias[] = $media;
        return $this;
    }
}
