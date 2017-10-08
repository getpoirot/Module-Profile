<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class ListFollowersReqsAction
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
     * Send Follow Request To An User
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


        # List Whole Pending Follow Requests
        #
        // TODO Implement Pagination
        $followRequests = $this->repoFollows->findAllForIncoming(
            $token->getOwnerIdentifier()
            , [
                EntityFollow::STAT_PENDING
            ]
        );


        # Build Response
        #
        $r = []; $c = 0;
        /** @var EntityFollow $f */
        foreach ($followRequests as $f) {
            $r[ (string) $f->getOutgoing() ] = [
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
            $profiles = $this->RetrieveProfiles(array_keys($r));
            foreach ($profiles as $uid => $user)
                $r[$uid]['user'] = $user;
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => $c,
                'items' => array_values($r),
            ]
        ];
    }
}
