<?php
return [
    'services' => [
        'RegisterAction'            => \Module\Profile\Actions\RegisterAction::class,
        'GetBasicProfileAction'     => \Module\Profile\Actions\GetBasicProfileAction::class,
        'GetFullProfileAction'      => \Module\Profile\Actions\GetFullProfileAction::class,
        'GetMyProfileAction'        => \Module\Profile\Actions\GetMyProfileAction::class,

        'UploadAvatarAction'        => \Module\Profile\Actions\UploadAvatarAction::class,
        'DeleteAvatarAction'        => \Module\Profile\Actions\DeleteAvatarAction::class,
        'RetrieveAvatarAction'      => \Module\Profile\Actions\RetrieveAvatarAction::class,
        'RetrieveUserAvatarAction'  => \Module\Profile\Actions\RetrieveUserAvatarAction::class,
        'RenderProfilePicAction'    => \Module\Profile\Actions\RenderProfilePicAction::class,

        'SendFollowRequestAction'   => \Module\Profile\Actions\Interact\FollowAction::class,
        'ListFollowersReqsAction'   => \Module\Profile\Actions\Interact\ListFollowersReqsAction::class,
        'ListFollowingsReqsAction'  => \Module\Profile\Actions\Interact\ListFollowingsReqsAction::class,
        'AcceptRequestAction'       => \Module\Profile\Actions\Interact\AcceptRequestAction::class,
        'DenyFollowRequestAction'   => \Module\Profile\Actions\Interact\DenyFollowRequestAction::class,
        'CancelFollowingReqAction'  => \Module\Profile\Actions\Interact\CancelFollowingReqAction::class,
        'GetMyFollowersAction'      => \Module\Profile\Actions\Interact\GetMyFollowersAction::class,
        'GetMyFollowingsAction'     => \Module\Profile\Actions\Interact\GetMyFollowingsAction::class,
        'GetUserFollowersAction'    => \Module\Profile\Actions\Interact\GetUserFollowersAction::class,
        'GetUserFollowingsAction'   => \Module\Profile\Actions\Interact\GetUserFollowingsAction::class,
        'RemoveFromFriendsAction'   => \Module\Profile\Actions\Interact\RemoveFromFriendsAction::class,
        'KickUserAction'            => \Module\Profile\Actions\Interact\KickUserAction::class,

        'ListUsersProfile'          => \Module\Profile\Actions\Helpers\RetrieveProfiles::class,
    ],
];
