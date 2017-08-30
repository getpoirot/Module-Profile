<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class EntityFollow
    extends \Module\Profile\Model\Entity\EntityFollow
    implements Persistable
{
    use tPersistable;

    /** @var  \MongoId */
    protected $_id;


    function setUid($uid)
    {
        return $this->set_Id($uid);
    }

    function getUid()
    {
        return $this->get_Id();
    }

    // Mongonize Options

    function set_Id($id)
    {
        $this->_id = $id;
    }

    function get_Id()
    {
        return $this->_id;
    }

    function set__Pclass()
    {
        // Ignore Values
    }

    /**
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateTimeCreatedMongo(UTCDatetime $date)
    {
        $this->setDateTimeCreated($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime
     */
    function getDateTimeCreatedMongo()
    {
        $dateTime = $this->getDateTimeCreated();
        return new UTCDatetime($dateTime->getTimestamp() * 1000);
    }

    /**
     * @override Ignore from persistence
     * @ignore
     *
     * Date Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        return parent::getDateTimeCreated();
    }

    /**
     * Set Updated Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateTimeUpdatedMongo(UTCDatetime $date)
    {
        $this->setDateTimeUpdated($date->toDateTime());
        return $this;
    }

    /**
     * Get Updated Date
     * note: persist when serialize
     *
     * @return UTCDatetime|null
     */
    function getDateTimeUpdatedMongo()
    {
        if ($dateTime = $this->getDateTimeUpdated())
            return new UTCDatetime($dateTime->getTimestamp() * 1000);

    }

    /**
     * @override Ignore from persistence
     * @ignore
     *
     * @return \DateTime|null
     */
    function getDateTimeUpdated()
    {
        return parent::getDateTimeUpdated();
    }
}
