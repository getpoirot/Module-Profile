<?php
use Module\HttpFoundation\Events\Listener\ListenerDispatch;

return [
    'profile'  => [
        'route' => 'RouteSegment',
        'options' => [
            'criteria'    => '/profile',
            'match_whole' => false,
        ],
        'params'  => [
            ListenerDispatch::ACTIONS => [
                // This Action Run First In Chains and Assert Validate Token
                //! define array allow actions on matched routes chained after this action
                /*
                 * [
                 *    [0] => Callable Defined HERE
                 *    [1] => routes defined callable
                 *     ...
                 */
                '/module/oauth2client/actions/AssertToken' => 'token',
            ],
        ],

        'routes' => [

            'avatars' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/avatars',
                    'match_whole' => false,
                ],
                'routes' =>
                    [
                        ## POST /profile/avatars/
                        #- Upload Avatar Profile Picture(s)
                        'create' => [
                            'route'   => 'RouteMethodSegment',
                            'options' => [
                                'criteria'    => '/',
                                'method'      => 'POST',
                                'match_whole' => true,
                            ],
                            'params'  => [
                                ListenerDispatch::ACTIONS => [
                                    '/module/profile/actions/UploadAvatarAction',
                                ],
                            ],
                        ],

                    ], // end avatars routes
            ], // end avatars

            ## POST /profile
            #- register user profile data
            'create' => [
                'route'   => 'RouteMethodSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/',
                    'method'   => 'POST',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/profile/actions/RegisterAction',
                    ],
                ],
            ],

        ], // end routes
    ] // end profiles
];
