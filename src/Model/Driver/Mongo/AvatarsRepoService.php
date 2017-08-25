<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class AvatarsRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Avatars';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return AvatarsRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new AvatarsRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
