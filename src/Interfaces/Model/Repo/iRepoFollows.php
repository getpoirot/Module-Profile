<?php
namespace Module\Profile\Interfaces\Model\Repo;

use Module\Profile\Model\Entity\EntityFollow;


interface iRepoFollows
{

    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     */
    function attainNextIdentifier($id = null);

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
    function save(EntityFollow $entity);

    /**
     * Find an Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return EntityFollow|null
     */
    function findOneByUID($uid);

    /**
     * Find One Interaction Between Receiver Of Request And Requester
     *
     * @param mixed $incoming
     * @param mixed $outgoing
     *
     * @return EntityFollow|null
     */
    function findOneWithInteraction($incoming, $outgoing);

    /**
     * Find All Follows Has Specific Status
     *
     * @param array  $status
     * @param string $offset
     * @param int    $limit
     *
     * @return \Traversable
     */
    function findAllHasStatus(array $status, $offset = null, $limit = null);

    /**
     * Find All Follow Requests Match Incoming UID
     *
     * @param mixed $incoming
     * @param array $status   If given filter for these specific status
     *
     * @return \Traversable
     */
    function findAllForIncoming($incoming, array $status = null);

    /**
     * Get Count All Incoming Request For
     *
     * @param $incoming
     * @param array|null $status
     *
     * @return int
     */
    function getCountAllForIncoming($incoming, array $status = null);

    /**
     * Find All Follow Requests Match Outgoing UID
     *
     * @param mixed $outgoing
     * @param array $status   If given filter for these specific status
     *
     * @return \Traversable
     */
    function findAllForOutgoings($outgoing, array $status = null);

    /**
     * Get Count All Outgoing Request For
     *
     * @param $outgoing
     * @param array|null $status
     *
     * @return int
     */
    function getCountAllForOutgoing($outgoing, array $status = null);

    /**
     * Delete Entity By Given Id
     *
     * @param mixed $followId
     *
     * @return int
     */
    function deleteById($followId);
}
