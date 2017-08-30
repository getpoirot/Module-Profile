<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Driver\Mongo\EntityProfile;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class FollowAction
    extends aAction
{
    /** @var iRepoFollows */
    protected $repoFollows;
    /** @var iRepoProfiles */
    protected $repoProfiles;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     */
    function __construct(iHttpRequest $httpRequest, iRepoFollows $repoFollows, iRepoProfiles $repoProfiles)
    {
        parent::__construct($httpRequest);

        $this->repoFollows  = $repoFollows;
        $this->repoProfiles = $repoProfiles;
    }


    /**
     * Send Follow Request To An User
     *
     * @param iAccessToken $token
     * @param string       $username Uri param
     * @param string       $userid   Uri param
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null, $username = null, $userid = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Consider User With Given Username
        #
        if ($username !== null) {
            // Retrieve User Info From OAuth By username
            $oauthInfo = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($username) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUsername($username);

                return $info;
            });

            $userid = $oauthInfo['user']['uid'];

        }

        # Persist Follow Request
        #
        $entity = new EntityFollow;
        $entity
            ->setIncoming($userid)
            ->setOutgoing($token->getOwnerIdentifier())
        ;

        // Check whether Receiver Of Request Is Private or Public
        $followStatus = EntityFollow::STAT_ACCEPTED;

        $stat = $this->repoProfiles->getUserPrivacyStatByUid($userid);
        if ($stat !== null && $stat != EntityProfile::PRIVACY_PUBLIC)
            // Profile hs not public privacy so persist request and wait for confirm
            $followStatus = EntityFollow::STAT_PENDING;


        $entity->setStat($followStatus);


        # Persist Follow Request
        #
        // Leave Untouched if we have same request for these interaction
        // Check Interaction between Incoming(Receiver) and Outgoing(Requester).
        if (! $e = $this->repoFollows->findOneWithInteraction($userid, $token->getOwnerIdentifier()) )
            $entity = $this->repoFollows->save($entity);
        else
            $entity = $e;


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'status' => $entity->getStat(),
                '_self'  => [
                    'outgoing' => $userid,
                ],
            ],
        ];
    }
}
