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

            ## POST /profile
            #- register user profile data
            'post' => [
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
