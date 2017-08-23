<?php
use Module\MongoDriver\Services\aServiceRepository;

return [

    # Mongo Driver:

    Module\MongoDriver\Module::CONF_KEY =>
    [
        aServiceRepository::CONF_REPOSITORIES =>
        [
            \Module\Profile\Model\Driver\Mongo\ProfilesRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'profile.users',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id' => 1]],

                    ],],],
        ],
    ],
];
