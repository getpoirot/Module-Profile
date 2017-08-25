<?php
return [
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' => [
                'Profiles' => \Module\Profile\Model\Driver\Mongo\ProfilesRepoService::class,
                'Avatars'  => \Module\Profile\Model\Driver\Mongo\AvatarsRepoService::class,
            ],
        ],
    ],
];
