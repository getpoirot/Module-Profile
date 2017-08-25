<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use Module\Profile\Avatars\FactoryMediaObject;
use MongoDB\BSON\Persistable;


class EntityAvatar
    extends \Module\Profile\Model\Entity\EntityAvatar
    implements Persistable
{
    use tPersistable;

    /** @var  \MongoId */
    protected $_id;


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
        if (isset($data['medias'])) {
            foreach ($data['medias'] as $media)
                $this->addMedia( FactoryMediaObject::of($media) );

            unset($data['medias']);
        }


        $this->import($data);
    }
}
