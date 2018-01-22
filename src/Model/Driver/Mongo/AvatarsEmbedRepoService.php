<?php
namespace Module\Profile\Model\Driver\Mongo;

use Module\MongoDriver\Services\aServiceRepository;


/**
 * Embed Avatar Into Profile Repo
 *
 */
class AvatarsEmbedRepoService
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'AvatarsEmbed';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return AvatarsEmbedRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        $repo = new AvatarsEmbedRepo($mongoDb, $collection, $persistable);
        return $repo;
    }
}
