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
                        // TODO Put Indexes
                    ],],],

            \Module\Profile\Model\Driver\Mongo\AvatarsRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'profile.users.avatars',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id' => 1]],
                        // TODO Put Indexes
                    ],],],

            \Module\Profile\Model\Driver\Mongo\FollowsRepoService::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'profile.follows',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['_id' => 1]],
                        // TODO Put Indexes
                        // TODO Compound Index incoming-outgoing unique remove duplicates
                    ],],],
        ],
    ],
];
