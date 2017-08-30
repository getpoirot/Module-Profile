<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class FollowsRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Follows';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return FollowsRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new FollowsRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
