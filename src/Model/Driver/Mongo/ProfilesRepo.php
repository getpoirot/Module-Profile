<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\ReadPreference;
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
        $entity
            ->setUid( $this->attainNextIdentifier($profileEntity->getUid()) )
            ->setDisplayName( $profileEntity->getDisplayName() )
            ->setBio( $profileEntity->getBio() )
            ->setLocation( $profileEntity->getLocation() )
            ->setGender( $profileEntity->getGender() )
            ->setPrivacyStatus( $profileEntity->getPrivacyStatus() )
            ->setBirthday( $profileEntity->getBirthday() )
            ->setDateTimeCreated( $profileEntity->getDateTimeCreated() )
        ;


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
            ->setPrivacyStatus( $entity->getPrivacyStatus() )
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
            ->setPrivacyStatus( $entity->getPrivacyStatus() )
            ->setBirthday( $entity->getBirthday() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
        ;

        return $rEntity;
    }

    /**
     * Find All Users Match By Given UIDs
     *
     * @param array $uids
     *
     * @return iEntityProfile[]
     */
    function findAllByUIDs(array $uids)
    {
        $uids = array_values($uids);

        foreach ($uids as $i => $v )
            $uids[$i] = $this->attainNextIdentifier($v);

        /** @var iEntityProfile $r */
        $crsr = $this->_query()->find([
            'uid' => [
                '$in' => $uids,
            ],
        ]);


        return $crsr;
    }

    /**
     * Retrieve User Privacy Stat By Given UID
     *
     * @param mixed $uid
     *
     * @return string|null
     */
    function getUserPrivacyStatByUid($uid)
    {
        $e = $this->_query()->findOne(
            [
                'uid' => $this->attainNextIdentifier($uid)
            ]
            , [
                'projection' => [
                    'privacy_status' => 1,
                ],
                'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
            ]
        );

        return $e;
    }

    /**
     * Find All Entities Match With Given Expression
     *
     * $exp: [
     *   'uid'         => ..,
     *   'display_name' => ..,
     *   'privacy_status'      => ...
     * ]
     *
     * @param array $expr
     * @param string $offset
     * @param int $limit
     *  @param string|integer  $sort (if driver is mongo sort define as int else define desc or asc)
     *
     * @return \Traversable
     */

    function findAll($expr, $limit, $offset,$sort = self::MONGO_SORT_DESC)
    {
        # search term to mongo condition
        $condition = \Module\MongoDriver\buildMongoConditionFromExpression($expr);

        if ($offset)
            $condition = [
                    'uid' => [
                        '$lt' => $this->attainNextIdentifier($offset),
                    ]
                ] + $condition;

        $r = $this->_query()->find(
            $condition
            , [
                'limit' => $limit,
                'sort'  => [
                    '_id' => $sort,
                ]
            ]
        );

        return $r;
    }

    /**
     * @param $limit
     * @param $offset
     * @return \Traversable
     */
    function findAllHaveAvatar($limit, $offset)
    {
        $condition=[];
        if ($offset)
            $condition = [
                    'uid' => [
                        '$lt' => $this->attainNextIdentifier($offset),
                    ]
                ] ;

        $r = $this->_query()->find(
            $condition
            , [
                'limit' => $limit,
                'sort'  => [
                    '_id' => -1,
                ]
            ]
        );

        return $r;
    }
}
