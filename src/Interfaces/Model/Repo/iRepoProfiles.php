<?php
namespace Module\Profile\Interfaces\Model\Repo;

use Module\Profile\Interfaces\Model\Entity\iEntityProfile;


interface iRepoProfiles
{

    const MONGO_SORT_ASC  = 1;
    const MONGO_SORT_DESC = -1;

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
     * @param iEntityProfile $profileEntity
     *
     * @return iEntityProfile
     */
    function save(iEntityProfile $profileEntity);

    /**
     * Find Entity By Given UID
     *
     * @param mixed $uid
     *
     * @return iEntityProfile|null
     */
    function findOneByUID($uid);

    /**
     * Find All Users Match By Given UIDs
     *
     * @param array $uids
     *
     * @return iEntityProfile[]
     */
    function findAllByUIDs(array $uids);

    /**
     * Retrieve User Privacy Stat By Given UID
     *
     * @param mixed $uid
     *
     * @return string|null
     */
    function getUserPrivacyStatByUid($uid);


    /**
     * Find All Entities Match With Given Expression
     *
     * $exp: [
     *   'owner_id'         => ..,
     *   'wallet_type' => ..,
     *   'target'      => ...
     * ]
     *
     * @param array $expr
     * @param string $offset
     * @param int $limit
     * @param integer $sort
     *
     * @return \Traversable
     */
    function findAll($expr , $limit , $offset ,$sort);
}
