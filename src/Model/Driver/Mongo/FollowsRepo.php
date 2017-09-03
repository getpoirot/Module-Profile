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
        $e = new Mongo\EntityFollow();
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
     * Find All Follow Requests Match Incoming UID
     *
     * @param mixed $incoming
     * @param array $status   If given filter for these specific status
     *
     * @return \Traversable
     */
    function findAllForIncoming($incoming, array $status = null)
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


        $crsr = $this->_query()->find($condition, [
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }

    /**
     * Find All Follow Requests Match Outgoing UID
     *
     * @param mixed $incoming
     * @param array $status   If given filter for these specific status
     *
     * @return \Traversable
     */
    function findAllForOutgoings($incoming, array $status = null)
    {
        $condition = [
            'outgoing' => $this->attainNextIdentifier($incoming)
        ];

        if ($status) {
            $or = [];
            foreach ($status as $s)
                $or[] = [ 'stat' =>  $s ];

            $condition += [ '$or' => $or ];
        }


        $crsr = $this->_query()->find($condition, [
            'readPreference' => new ReadPreference(ReadPreference::RP_NEAREST)
        ]);

        return $crsr;
    }
}
