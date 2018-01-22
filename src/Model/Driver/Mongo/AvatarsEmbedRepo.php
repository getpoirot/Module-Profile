<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Model\Driver\Mongo;
use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Model\Entity\EntityAvatar;
use MongoDB\BSON\ObjectID;
use Poirot\Std\Type\StdTravers;
use Poirot\TenderBinClient\Model\aMediaObject;


class AvatarsEmbedRepo
    extends aRepository
{
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
     * Embed Entity Avatar Into Profiles
     *
     * @param EntityAvatar $entity
     *
     * @return EntityAvatar
     */
    function save(EntityAvatar $entity)
    {
        $primary = $entity->getPrimary();

        /** @var aMediaObject $media */
        foreach ($entity->getMedias() as $media) {
            if ($media->getHash() == $primary) {
                $primary = $media;
                break;
            }
        }

        if ($primary)
            $primary = StdTravers::of($primary)->toArray();

        /** @var Mongo\EntityAvatar $entity */
        $entity = $this->_query()->findOneAndUpdate(
            [
                'uid' => $this->attainNextIdentifier( $entity->getUid() ),
            ]
            , [
                '$set' => [
                    'primary_avatar' => $primary,
                ],
            ]
            , [ 'upsert' => true, ]
        );


        return $entity;
    }
}
