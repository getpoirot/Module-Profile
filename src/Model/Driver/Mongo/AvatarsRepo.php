<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\Driver\Mongo;
use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Model\Entity\EntityAvatar;
use MongoDB\BSON\ObjectID;
use MongoDB\Operation\FindOneAndUpdate;
use Poirot\Std\Type\StdTravers;


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
     * Retrieve Avatar Entity By UID
     *
     * @param mixed $uid Owner ID
     *
     * @return EntityAvatar|null
     */
    function findOneByUid($uid)
    {
        $entity = $this->_query()->findOne([
            'uid' => $this->attainNextIdentifier( $uid ),
        ]);


        if (! $entity )
            // Not Found ...
            return null;

        $rEntity = new EntityAvatar;
        $rEntity
            ->setUid( $entity->getUid() )
            ->setPrimary( $entity->getPrimary() )
            ->setMedias( $entity->getMedias() )
        ;

        return $rEntity;
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
            ->setPrimary( $entity->getPrimary() )
            ->setMedias( $entity->getMedias() )
        ;


        $medias = $e->getMedias();
        foreach ($medias as $i => $m)
            $medias[$i] = StdTravers::of($m)->toArray();

        /** @var Mongo\EntityAvatar $entity */
        $entity = $this->_query()->findOneAndUpdate(
            [
                'uid' => $this->attainNextIdentifier( $entity->getUid() ),
            ]
            , [
                '$set' => [
                    'uid'     => $this->attainNextIdentifier( $entity->getUid() ),
                    'primary' => $entity->getPrimary(),
                ],
                '$push' => [
                    'medias' => [
                        '$each'     => $medias,
                        '$position' => 0,
                    ],
                ],
            ]
            , [ 'upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER, ]
        );


        $rEntity = new EntityAvatar;
        $rEntity
            ->setUid( $entity->getUid() )
            ->setPrimary( $entity->getPrimary() )
            ->setMedias( $entity->getMedias() )
        ;

        return $rEntity;
    }

    /**
     * Remove an avatar from list by given hash id
     *
     * @param mixed $uid
     * @param mixed $mediaHash
     *
     * @return void
     */
    function delUserAvatarByHash($uid, $mediaHash)
    {
        $this->_query()->findOneAndUpdate(
            [
                'uid' => $this->attainNextIdentifier( $uid ),
            ]
            , [
                '$pull' => [
                    'medias' => [ 'hash' => $mediaHash ]
                ],
            ]
        );
    }
}
