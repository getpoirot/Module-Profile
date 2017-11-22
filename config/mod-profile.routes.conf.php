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
            // TODO with cli
            'cron' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/cron/cleanup',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        \Module\Profile\Actions\Cron\CronCleanupRequestsAction::class,
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

                                ## PUT /profile/avatars/
                                'modify' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/',
                                        'method'      => 'PUT',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/ModifyAvatarAction',
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

            ## GET /profile
            #- retrieve user profile data
            'get' => [
                'route'   => 'RouteMethodSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/',
                    'method'   => 'GET',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/profile/actions/GetMyProfileAction',
                    ],
                ],
            ],

            ## GET /profile/followers
            'followers' => [
                'route'   => 'RouteMethodSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/followers',
                    'method'   => 'GET',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/profile/actions/GetMyFollowersAction',
                    ],
                ],
            ],

            ## GET /profile/followers/requests
            #- list follows requests
            'followersRequests' => [
                'route'   => 'RouteSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/followers/requests',
                    'match_whole' => false,
                ],

                'routes' => [

                    ## GET /profile/follows/requests
                    #- Retrieve Avatar Profile Picture(s)
                    'listRequests' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria'    => '/',
                            'method'      => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/ListFollowersReqsAction',
                            ],
                        ],
                    ],

                    'delegate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // 24 is length of user_id by persistence
                            'criteria'    => '/<:request_id~\w{24}~>',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'accept' => [
                                'route'   => 'RouteMethodSegment',
                                'options' => [
                                    'criteria'    => '/',
                                    'method'      => 'POST',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::ACTIONS => [
                                        '/module/profile/actions/AcceptRequestAction',
                                    ],
                                ],
                            ],
                            'deny' => [
                                'route'   => 'RouteMethodSegment',
                                'options' => [
                                    'criteria'    => '/',
                                    'method'      => 'DELETE',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::ACTIONS => [
                                        '/module/profile/actions/DenyFollowRequestAction',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            ## GET /profile/followings
            'followings' => [
                'route'   => 'RouteMethodSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/followings',
                    'method'   => 'GET',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/profile/actions/GetMyFollowingsAction',
                    ],
                ],
            ],

            ## GET /profile/following/requests
            #- list follows requests
            'followingRequests' => [
                'route'   => 'RouteSegment',
                'options' => [
                    // 24 is length of content_id by persistence
                    'criteria' => '/followings/requests',
                    'match_whole' => false,
                ],

                'routes' => [

                    ## GET /profile/follows/requests
                    #- Retrieve Avatar Profile Picture(s)
                    'listRequests' => [
                        'route'   => 'RouteMethodSegment',
                        'options' => [
                            'criteria'    => '/',
                            'method'      => 'GET',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/ListFollowingsReqsAction',
                            ],
                        ],
                    ],

                    'delegate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // 24 is length of user_id by persistence
                            'criteria'    => '/<:request_id~\w{24}~>',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'cancel' => [
                                'route'   => 'RouteMethodSegment',
                                'options' => [
                                    'criteria'    => '/',
                                    'method'      => 'DELETE',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::ACTIONS => [
                                        '/module/profile/actions/CancelFollowingReqAction',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'delegate' => [
                'route' => 'RouteSegment',
                'options' => [
                    // 24 is length of user_id by persistence
                    'criteria'    => '/<u/:username~[a-zA-Z0-9._]+~><-:userid~\w{24}~>',
                    'match_whole' => false,
                ],
                'routes' => [

                    ## GET /profile/{{user}}/page
                    #- user profile page
                    'profile_page' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/page',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/GetProfilePageAction',
                            ],
                        ],
                    ],

                    ## GET /profile/{{user}}/full
                    #- user full profile
                    'profile_full' => [
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

                    ## GET /profile/{{user}}/basic
                    #- user basic profile
                    'profile_basic' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/basic',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/GetBasicProfileAction',
                            ],
                        ],
                    ],

                    ## GET /profile/{{user}}/followers
                    #- user basic profile
                    'followers' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/followers',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/GetUserFollowersAction',
                            ],
                        ],
                    ],

                    ## GET /profile/{{user}}/followings
                    #- user basic profile
                    'followings' => [
                        'route'   => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/followings',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/profile/actions/GetUserFollowingsAction',
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

                    'interaction' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/go',
                            'match_whole' => false,
                        ],
                        'routes' =>
                            [

                                ## GET /profile/{{user}}/go/follow
                                'follow' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/follow',
                                        'method'      => 'GET',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/SendFollowRequestAction',
                                        ],
                                    ],
                                ],

                                ## GET /profile/{{user}}/go/remove
                                'remove' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/remove',
                                        'method'      => 'GET',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/RemoveFromFriendsAction',
                                        ],
                                    ],
                                ],

                                ## GET /profile/{{user}}/go/kick
                                'kick' => [
                                    'route'   => 'RouteMethodSegment',
                                    'options' => [
                                        'criteria'    => '/kick',
                                        'method'      => 'GET',
                                        'match_whole' => true,
                                    ],
                                    'params'  => [
                                        ListenerDispatch::ACTIONS => [
                                            '/module/profile/actions/KickUserAction',
                                        ],
                                    ],
                                ],

                            ], // end interaction routes
                    ], // end interaction

                ], // end avatars delegate routes
            ], // end avatars delegate

        ], // end routes
    ] // end profiles
];
