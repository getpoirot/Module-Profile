<?php
use Module\MongoDriver\Services\aServiceRepository;
use Module\Profile\Events\EventsHeapOfProfile;

return [

    Module\Profile\Module::CONF => [

        ## Events
        #
        \Module\Profile\Actions\aAction::CONF => [
            // Events Section Of Events Builder
            /** @see \Poirot\Events\Event\BuildEvent */

            EventsHeapOfProfile::RETRIEVE_PROFILE_RESULT => [
                'listeners' => [
                    ['priority' => 1000,  'listener' => function($entityProfile, $visitor) {
                        // Implement this
                        /** @var \Module\Profile\Model\Entity\EntityProfile $entityProfile */
                    }],
                ],
            ],

        ],
    ],

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
