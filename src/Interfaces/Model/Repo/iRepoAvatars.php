<?php
namespace Module\Profile\Interfaces\Model\Repo;

use Module\Profile\Model\Entity\EntityAvatar;


interface iRepoAvatars
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
     * Retrieve Avatar Entity By UID
     *
     * @param mixed $uid Owner ID
     *
     * @return EntityAvatar|null
     */
    function findOneByOwnerUid($uid);

    /**
     * Save Entity By Insert Or Update
     *
     * @param EntityAvatar $entity
     *
     * @return EntityAvatar
     */
    function save(EntityAvatar $entity);

    /**
     * Remove an avatar from list by given hash id
     *
     * @param mixed $uid
     * @param mixed $mediaHash
     *
     * @return EntityAvatar
     */
    function delUserAvatarByHash($uid, $mediaHash);
}
