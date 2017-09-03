<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class CancelFollowingReqAction
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
     * Change Follow Request Status
     *
     * @param iAccessToken $token
     * @param mixed        $request_id
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null, $request_id = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Retrieve Follow Request
        #
        $followRequest = $this->repoFollows->findOneByUID($request_id);
        if (! $followRequest )
            // Not Request Found!
            throw new exRouteNotMatch;


        # Accept Status From Request
        #
        // Check that request is for token owner
        if ( (string) $followRequest->getOutgoing() != (string) $token->getOwnerIdentifier() )
            throw new \Exception('Bad Request.', 400);

        // Persist entity
        if ($followRequest->getStat() != EntityFollow::STAT_REJECTED) {
            $followRequest->setStat(EntityFollow::STAT_REJECTED);
            $followRequest = $this->repoFollows->save($followRequest);
        }


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'status' => $followRequest->getStat(),
                '_self'  => [
                    'request_id' => $request_id,
                ],
            ],
        ];
    }
}
