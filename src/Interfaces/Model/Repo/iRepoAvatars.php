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
     * Save Entity By Insert Or Update
     *
     * @param EntityAvatar $entity
     *
     * @return EntityAvatar
     */
    function save(EntityAvatar $entity);
}
