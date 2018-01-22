<?php
return [
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' => [
                'Profiles'     => \Module\Profile\Model\Driver\Mongo\ProfilesRepoService::class,

                'Follows'      => \Module\Profile\Model\Driver\Mongo\FollowsRepoService::class,

                'Avatars'      => \Module\Profile\Model\Driver\Mongo\AvatarsRepoService::class,
                'AvatarsEmbed' => [
                    \Module\Profile\Model\Driver\Mongo\AvatarsEmbedRepoService::class,
                    'mongo_collection' => 'profile.users', // same as profile
                ],

            ],
        ],
    ],
];
