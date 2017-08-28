<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use MongoDB\BSON\ObjectID;
use MongoDB\Operation\FindOneAndUpdate;


class ProfilesRepo
    extends aRepository
    implements iRepoProfiles
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->persist )
            $this->setModelPersist(new Mongo\EntityProfile);
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
     * @param iEntityProfile $profileEntity
     *
     * @return iEntityProfile
     */
    function save(iEntityProfile $profileEntity)
    {
        $entity = new Mongo\EntityProfile();
        $entity->setUid( $this->attainNextIdentifier($profileEntity->getUid()) );
        $entity->setDisplayName( $profileEntity->getDisplayName() );
        $entity->setBio( $profileEntity->getBio() );
        $entity->setLocation( $profileEntity->getLocation() );
        $entity->setGender( $profileEntity->getGender() );
        $entity->setBirthday( $profileEntity->getBirthday() );
        $entity->setDateTimeCreated( $profileEntity->getDateTimeCreated() );



        /** @var \Module\Profile\Model\Driver\Mongo\EntityProfile $entity */
        $entity = $this->_query()->findOneAndUpdate(
            [
                'uid' => $this->attainNextIdentifier( $entity->getUid() ),
            ]
            , [
                '$set' => $entity,
            ]
            , [ 'upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER, ]
        );


        $rEntity = new EntityProfile;
        $rEntity
            ->setUid( $entity->getUid() )
            ->setDisplayName( $entity->getDisplayName() )
            ->setBio( $entity->getBio() )
            ->setLocation( $entity->getLocation() )
            ->setGender( $entity->getGender() )
            ->setBirthday( $entity->getBirthday() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
        ;

        return $rEntity;
    }

    /**
     * Find Entity By Given UID
     *
     * @param mixed $uid
     *
     * @return iEntityProfile|null
     */
    function findOneByUID($uid)
    {
        /** @var Mongo\EntityProfile $entity */
        $entity = $this->_query()->findOne([
            'uid' => $this->attainNextIdentifier( $uid ),
        ]);


        if (! $entity )
            // Not Found ...
            return null;

        $rEntity = new EntityProfile;
        $rEntity
            ->setUid( $entity->getUid() )
            ->setDisplayName( $entity->getDisplayName() )
            ->setBio( $entity->getBio() )
            ->setLocation( $entity->getLocation() )
            ->setGender( $entity->getGender() )
            ->setBirthday( $entity->getBirthday() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
        ;

        return $rEntity;
    }
}
