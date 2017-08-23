<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Entity\Profile\GeoObject;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class EntityProfile
    extends \Module\Profile\Model\Entity\EntityProfile
    implements iEntityProfile
    , Persistable
{
    use tPersistable;


    // Mongonize DateCreated

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
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setBirthdayMongo(UTCDatetime $date)
    {
        $this->setBirthday($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime|null
     */
    function getBirthdayMongo()
    {
        if ($dateTime = $this->getBirthday())
            return new UTCDatetime($dateTime->getTimestamp() * 1000);

    }

    /**
     * @override Ignore from persistence
     * @ignore
     *
     * @return \DateTime|null
     */
    function getBirthday()
    {
        return parent::getBirthday();
    }


    // ...

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    function bsonUnserialize(array $data)
    {
        if (isset($data['location']))
            // Unserialize BsonDocument to Required GeoObject from Persistence
            $data['location'] = new GeoObject($data['location']);


        $this->import($data);
    }
}
