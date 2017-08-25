<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\Driver\Mongo;
use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Model\Entity\EntityAvatar;
use MongoDB\BSON\ObjectID;
use MongoDB\Operation\FindOneAndUpdate;


class AvatarsRepo
    extends aRepository
    implements iRepoAvatars
{
    protected $typeMap = [
        'document' => \MongoDB\Model\BSONArray::class , // !! traversable object to fully serialize to array
    ];

    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->persist )
            $this->setModelPersist( new Mongo\EntityAvatar );
    }

    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     * @throws \Exception
     */
    function attainNextIdentifier($id = null)
    {
        try {
            $objectId = ($id !== null) ? new ObjectID( (string)$id ) : new ObjectID;
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Invalid Persist (%s) Id is Given.', $id));
        }

        return $objectId;
    }


    /**
     * Save Entity By Insert Or Update
     *
     * @param EntityAvatar $entity
     *
     * @return EntityAvatar
     */
    function save(EntityAvatar $entity)
    {
        $e = new Mongo\EntityAvatar;
        $e
            ->setUid( $this->attainNextIdentifier($entity->getUid()) )
            ->setMedias( $entity->getMedias() )
        ;

        /** @var Mongo\EntityAvatar $entity */
        $entity = $this->_query()->findOneAndUpdate(
            [
                'uid' => $this->attainNextIdentifier( $entity->getUid() ),
            ]
            , [
                '$set' => $e,
            ]
            , [ 'upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER, ]
        );


        $rEntity = new EntityAvatar;
        $rEntity
            ->setUid( $entity->getUid() )
            ->setMedias( $entity->getMedias() )
        ;

        return $rEntity;
    }
}
