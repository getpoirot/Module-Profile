<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;
use Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
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


        return $entity;
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


        return $entity;
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
        /** @var iEntityProfile $e */
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

        return ($e) ? (string) $e->getPrivacyStatus() : null;
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

    function findAll(array $expr, $limit, $offset,$sort = self::MONGO_SORT_DESC)
    {
        # search term to mongo condition
        $expr      = \Module\MongoDriver\parseExpressionFromArray($expr);
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
     * Find All Users Has Avatar Profile
     *
     * @param $limit
     * @param $offset
     *
     * @return \Traversable
     */
    function findAllHaveAvatar($limit = 30, $offset = null)
    {
        $condition = [];

        if ($offset)
            $condition = [
                '_id' => [ '$lt' => $this->attainNextIdentifier($offset), ]
            ];

        $q = [
            [ '$sort' => ['_id' => -1], ],
            [ '$limit' => $limit ],
            [ '$lookup' => [
                'from'         => 'profile.users.avatars',
                'localField'   => 'uid',
                'foreignField' => 'uid',
                'as'           => 'avatars', ], ],
            [ '$unwind' => '$avatars'], // Only Users Who Have Avatar
            [
                '$project' => [
                    'uid' => 1,  'display_name' => 1,  'gender' => 1,
                    'privacy_status' => 1,
                ],
            ],
        ];

        if (! empty($condition) )
            array_unshift($q, $condition);

        $r = $this->_query()->aggregate($q);
        return $r;
    }
}
