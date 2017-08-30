<?php
return [
    'services' => [
        'RegisterAction'           => \Module\Profile\Actions\RegisterAction::class,
        'GetBasicProfileAction'    => \Module\Profile\Actions\GetBasicProfileAction::class,
        'GetFullProfileAction'     => \Module\Profile\Actions\GetFullProfileAction::class,
        'GetMyProfileAction'       => \Module\Profile\Actions\GetMyProfileAction::class,

        'UploadAvatarAction'       => \Module\Profile\Actions\UploadAvatarAction::class,
        'DeleteAvatarAction'       => \Module\Profile\Actions\DeleteAvatarAction::class,
        'RetrieveAvatarAction'     => \Module\Profile\Actions\RetrieveAvatarAction::class,
        'RetrieveUserAvatarAction' => \Module\Profile\Actions\RetrieveUserAvatarAction::class,
        'RenderProfilePicAction'   => \Module\Profile\Actions\RenderProfilePicAction::class,

        'SendFollowRequestAction'  => \Module\Profile\Actions\Interact\FollowAction::class,
        'ListFollowRequestsAction' => \Module\Profile\Actions\Interact\ListFollowRequestsAction::class,

        'ListUsersProfile'         => \Module\Profile\Actions\Helpers\RetrieveProfiles::class,
    ],
];
