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

                        ## GET /profile/avatars/
                        #- Retrieve Avatar Profile Picture(s)
                        'retrieve' => [
                            'route'   => 'RouteMethodSegment',
                            'options' => [
                                'criteria'    => '/',
                                'method'      => 'GET',
                                'match_whole' => true,
                            ],
                            'params'  => [
                                ListenerDispatch::ACTIONS => [
                                    '/module/profile/actions/RetrieveAvatarAction',
                                ],
                            ],
                        ],

                        'delegate' => [
                            'route' => 'RouteSegment',
                            'options' => [
                                // 24 is length of content_id by persistence
                                'criteria'    => '/:hash_id~\w{24}~',
                                'match_whole' => false,
                            ],
                            'routes' => [

                                ## DELETE /profile/avatar/{{hash_id}}
                                #- Delete an avatar image by currently authenticated user.
                                'delete' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/',
                                        'method'      => 'DELETE',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/DeleteAvatarAction',
                                        ],
                                    ],
                                ],

                            ], // end avatars delegate routes
                        ], // end avatars delegate

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

            'delegate' => [
                'route' => 'RouteSegment',
                'options' => [
                    // 24 is length of user_id by persistence
                    // TODO . in username not matched
                    'criteria'    => '/<@:username~\w+~><-:userid~\w{24}~>',
                    'match_whole' => false,
                ],
                'routes' => [

                    ## GET /profile/{{user}}/full
                    #- user basic profile
                    'retrieve' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/full',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/GetFullProfileAction',
                            ],
                        ],
                    ],

                    ## GET /profile/{{user}}/profile.jpg
                    #- Retrieve Avatar Profile Picture(s)
                    'profile_pic' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria'    => '/profile.jpg',
                            'method'      => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/RenderProfilePicAction',
                            ],
                        ],
                    ],

                    'avatars' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/avatars',
                            'match_whole' => false,
                        ],
                        'routes' =>
                            [

                                ## GET /profile/{{user}}/avatars/
                                #- Retrieve Avatar Profile Picture(s)
                                'retrieve' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/',
                                        'method'      => 'GET',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/RetrieveUserAvatarAction',
                                        ],
                                    ],
                                ],

                            ], // end avatars routes
                    ], // end avatars

                ], // end avatars delegate routes
            ], // end avatars delegate

        ], // end routes
    ] // end profiles
];
