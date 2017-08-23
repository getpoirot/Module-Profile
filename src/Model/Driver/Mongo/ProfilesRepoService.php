<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


class ProfilesRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Profiles';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return ProfilesRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new ProfilesRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
