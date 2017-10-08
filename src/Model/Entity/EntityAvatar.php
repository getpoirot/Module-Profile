<?php
namespace Module\Profile\Model\Entity;

use Poirot\Std\Struct\aDataOptions;
use Poirot\TenderBinClient\Model\aMediaObject;


class EntityAvatar
    extends aDataOptions
{
    protected $uid;
    /** @var aMediaObject[]  */
    protected $medias = [];
    protected $primary;
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
     * Set Primary Media By Hash ID
     *
     * @param mixed $hash
     *
     * @return $this
     */
    function setPrimary($hash)
    {
        $this->primary = $hash;
        return $this;
    }

    /**
     * Get Primary Media By Hash ID
     *
     * @return mixed|null
     */
    function getPrimary()
    {
        return $this->primary;
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
