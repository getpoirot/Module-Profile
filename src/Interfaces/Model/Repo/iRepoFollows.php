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
     * Find One Interaction Between Receiver Of Request And Requester
     *
     * @param mixed $incoming
     * @param mixed $outgoing
     *
     * @return EntityFollow|null
     */
    function findOneWithInteraction($incoming, $outgoing);

    /**
     * Find All Follow Requests Match Incoming UID
     *
     * @param mixed $incoming
     * @param array $status   If given filter for these specific status
     *
     * @return \Traversable
     */
    function findAllForIncoming($incoming, array $status = null);


}
