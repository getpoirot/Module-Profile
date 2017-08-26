<?php
return [
    'services' => [
        'RegisterAction'           => \Module\Profile\Actions\RegisterAction::class,
        'UploadAvatarAction'       => \Module\Profile\Actions\UploadAvatarAction::class,
        'DeleteAvatarAction'       => \Module\Profile\Actions\DeleteAvatarAction::class,
        'RetrieveAvatarAction'     => \Module\Profile\Actions\RetrieveAvatarAction::class,
        'RetrieveUserAvatarAction' => \Module\Profile\Actions\RetrieveUserAvatarAction::class,
        'RenderProfilePicAction'   => \Module\Profile\Actions\RenderProfilePicAction::class,
    ],
];
