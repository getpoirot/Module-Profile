<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetMyFollowingsAction
    extends aAction
{
    /** @var iRepoFollows */
    protected $repoFollows;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     */
    function __construct(iHttpRequest $httpRequest, iRepoFollows $repoFollows)
    {
        parent::__construct($httpRequest);

        $this->repoFollows  = $repoFollows;
    }


    /**
     * Retrieve Followers
     *
     * @param iAccessToken $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # List Whole Followers
        #
        // TODO Implement Pagination
        $followers = $this->repoFollows->findAllForOutgoings(
            $token->getOwnerIdentifier()
            , [
                'stat' => EntityFollow::STAT_ACCEPTED
            ]
        );


        # Build Response
        #
        $r = []; $c = 0;
        /** @var EntityFollow $f */
        foreach ($followers as $f) {
            $r[ (string) $f->getIncoming() ] = [
                'request_id' => (string) $f->getUid(),
                'created_on' => [
                    'datetime'  => $f->getDateTimeCreated(),
                    'timestamp' => $f->getDateTimeCreated()->getTimestamp(),
                ],
                'user' => [
                    /*
                    'uid'          => (string) $f->getOutgoing(),
                    'fullname'     => 'Payam Naderi',
                    'username'     => 'e1101',
                    'avatar'       => 'http://localhost:80/profile/-5997f286c39962003b255ba2/profile.jpg',
                    'privacy_stat' => 'public',
                    */
                ],
            ];

            $c++;
        }


        # Retrieve Users Account Info
        #
        if (! empty($r) ) {
            $profiles = \Module\Profile\Actions::RetrieveProfiles(array_keys($r));

            foreach ($r as $uid => $rq) {
                if (! isset($profiles[$uid]) ) {
                    unset($r[$uid]);
                    continue;
                }

                $r[$uid]['user'] = $profiles[$uid];
            }
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => $c,
                'items' => array_values($r),
            ]
        ];
    }
}
