<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Model\Driver\Mongo;
use Module\Profile\Model\Entity\EntityFollow;
use Module\MongoDriver\Model\Repository\aRepository;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\ReadPreference;
use MongoDB\Operation\FindOneAndUpdate;


class FollowsRepo
    extends aRepository
    implements iRepoFollows
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->persist )
            $this->setModelPersist( new Mongo\EntityFollow );
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
     * Persist Follow Request
     *
     * note: Only One Request With Each Unique Incoming-Outgoing
     *       Must Persist.
     *
     * @param EntityFollow $entity
     *
     * @return EntityFollow
     */
    function save(EntityFollow $entity)
    {
        $e = new Mongo\EntityFollow;
        $e
            ->setIncoming( $this->attainNextIdentifier($entity->getIncoming()) )
            ->setOutgoing( $this->attainNextIdentifier($entity->getOutgoing()) )
            ->setStat( $entity->getStat() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
            ->setDateTimeUpdated( new \DateTime )
        ;

        $entity = $this->_query()->findOneAndUpdate(
            [
                'incoming' => $this->attainNextIdentifier( $e->getIncoming() ),
                'outgoing' => $this->attainNextIdentifier( $e->getOutgoing() ),
            ]
            , [
                '$set' => $e,
            ]
            , [ 'upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER, ]
        );


        $rEntity = new EntityFollow;
        $rEntity
            ->setIncoming( $entity->getIncoming() )
            ->setOutgoing( $entity->getOutgoing() )
            ->setStat( $entity->getStat() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
            ->setDateTimeUpdated( $entity->getDateTimeUpdated() )
        ;

        return $rEntity;
    }

    /**
     * Find an Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return EntityFollow|null
     */
    function findOneByUID($uid)
    {
        $entity = $this->_query()->findOne(
            [
                '_id' => $this->attainNextIdentifier( $uid ),
            ]
        );


        if (! $entity )
            return null;

        $rEntity = new EntityFollow;
        $rEntity
            ->setIncoming( $entity->getIncoming() )
            ->setOutgoing( $entity->getOutgoing() )
            ->setStat( $entity->getStat() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
            ->setDateTimeUpdated( $entity->getDateTimeUpdated() )
        ;

        return $rEntity;
    }

    /**
     * Find One Interaction Between Receiver Of Request And Requester
     *
     * @param mixed $incoming Receiver  of follow request
     * @param mixed $outgoing Requester of follow request
     *
     * @return EntityFollow|null
     */
    function findOneWithInteraction($incoming, $outgoing)
    {
        $entity = $this->_query()->findOne(
            [
                'incoming' => $this->attainNextIdentifier( $incoming ),
                'outgoing' => $this->attainNextIdentifier( $outgoing ),
            ]
        );


        if (! $entity )
            return null;

        $rE = new EntityFollow;
        $rE
            ->setIncoming( $entity->getIncoming() )
            ->setOutgoing( $entity->getOutgoing() )
            ->setStat( $entity->getStat() )
            ->setDateTimeCreated( $entity->getDateTimeCreated() )
            ->setDateTimeUpdated( $entity->getDateTimeUpdated() )
        ;

        return $rE;
    }

    /**
     * Find All Follows Has Specific Status
     *
     * @param array  $status
     * @param string $offset
     * @param int    $limit
     *
     * @return \Traversable
     */
    function findAllHasStatus(array $status, $offset = null, $limit = null)
    {
        $condition = [];

        $or = [];
        foreach ($status as $s)
            $or[] = [ 'stat' =>  $s ];

        if ($offset)
            $condition = [
                '_id' => [
                    '$lt' => $this->attainNextIdentifier($offset), ]
            ] + $condition;

        $condition += [ '$or' => $or ];

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


    /**
     * Find All Follow Requests Match Incoming UID
     *
     * @param mixed  $incoming
     * @param array  $status   If given filter for these specific status
     * @param int    $limit
     * @param string $offset;
     * @param mixed  $sort
     *
     * @return \Traversable
     */
    function findAllForIncoming($incoming, array $status = null, $limit = 30, $offset = null, $sort = self::SORT_DESC)
    {
        $condition = [
            'incoming' => $this->attainNextIdentifier($incoming)
        ];

        if ($offset)
            $condition = [
                    '_id' => [
                        '$lt' => $this->attainNextIdentifier($offset),
                    ]
                ] + $condition;

        if ($status) {
            $or = [];
            foreach ($status as $s)
                $or[] = [ 'stat' =>  $s ];

            $condition += [ '$or' => $or ];
        }


        $crsr = $this->_query()->find($condition, [
            'limit' => $limit,
            'sort'  =>[
                '_id' => ($sort == self::SORT_DESC) ? -1 : 1,
            ],
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }

    /**
     * Get Count All Incoming Request For
     *
     * @param $incoming
     * @param array|null $status
     *
     * @return int
     */
    function getCountAllForIncoming($incoming, array $status = null)
    {
        $condition = [
            'incoming' => $this->attainNextIdentifier($incoming)
        ];

        if ($status) {
            $or = [];
            foreach ($status as $s)
                $or[] = [ 'stat' =>  $s ];

            $condition += [ '$or' => $or ];
        }


        $crsr = $this->_query()->count($condition, [
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }

    /**
     * Find All Follow Requests Match Outgoing UID
     *
     * @param mixed   $outgoing
     * @param array   $status   If given filter for these specific status
     * @param int     $limit
     * @param string  $offset;
     * @param mixed   $sort
     * @return \Traversable
     */
    function findAllForOutgoings($outgoing, array $status = null, $limit = 30, $offset = null, $sort=self::SORT_DESC)
    {
        $condition = [
            'outgoing' => $this->attainNextIdentifier($outgoing)
        ];

        if ($status) {
            $or = [];
            foreach ($status as $s)
                $or[] = [ 'stat' =>  $s ];

            $condition += [ '$or' => $or ];
        }

        if ($offset)
            $condition = [
                    '_id' => [
                        '$lt' => $this->attainNextIdentifier($offset),
                    ]
                ] + $condition;


        $crsr = $this->_query()->find($condition, [
            'limit' => $limit,
            'sort'  =>[
                '_id' => ($sort == self::SORT_DESC) ? -1 : 1,
            ],
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }

    /**
     * Get Count All Outgoing Request For
     *
     * @param $outgoing
     * @param array|null $status
     *
     * @return int
     */
    function getCountAllForOutgoing($outgoing, array $status = null)
    {
        $condition = [
            'outgoing' => $this->attainNextIdentifier($outgoing)
        ];

        if ($status) {
            $or = [];
            foreach ($status as $s)
                $or[] = [ 'stat' =>  $s ];

            $condition += [ '$or' => $or ];
        }


        $crsr = $this->_query()->count($condition, [
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }

    /**
     * Delete Entity By Given Id
     *
     * @param mixed $followId
     *
     * @return int
     */
    function deleteById($followId)
    {
        $r = $this->_query()->deleteOne([
            '_id' => $this->attainNextIdentifier($followId),
        ]);


        return $r->getDeletedCount();
    }
}
