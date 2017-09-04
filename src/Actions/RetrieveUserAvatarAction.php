<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RetrieveUserAvatarAction
    extends aAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = [

    ];

    /** @var iRepoAvatars */
    protected $repoAvatars;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoAvatars $repoAvatars @IoC /module/profile/services/repository/Avatars
     */
    function __construct(iHttpRequest $httpRequest, iRepoAvatars $repoAvatars)
    {
        parent::__construct($httpRequest);

        $this->repoAvatars = $repoAvatars;
    }


    /**
     * Delete Avatar By Owner
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

        if ($username !== null) {
            // Retrieve User ID From OAuth
            $userid = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($username) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUsername($username);

                return $info['user']['uid'];
            });
        }


        # Retrieve Avatars For User
        #
        $entity = $this->repoAvatars->findOneByOwnerUid( $userid );


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH =>
                \Module\Profile\Avatars\toArrayResponseFromAvatarEntity($entity)
        ];
    }
}
