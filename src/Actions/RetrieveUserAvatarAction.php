<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Application\Exception\exRouteNotMatch;
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
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     * @param iRepoAvatars $repoAvatars @IoC /module/profile/services/repository/Avatars
     * @param iRepoUsers   $users       @IoC /module/oauth2/services/repository/Users
     */
    function __construct(iHttpRequest $httpRequest, iRepoAvatars $repoAvatars, iRepoUsers $users)
    {
        parent::__construct($httpRequest);

        $this->repoAvatars = $repoAvatars;
        $this->repoUsers   = $users;
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
            // TODO from service as a client
            /** @var iOAuthUser $userEntity */
            $userEntity = $this->repoUsers->findOneMatchByIdentifiers([
                IdentifierObject::newUsernameIdentifier($username)
            ]);

            if (! $userEntity )
                throw new exRouteNotMatch;

            $userid = $userEntity->getUid();
        }


        # Retrieve Avatars For User
        #
        $entity = $this->repoAvatars->findOneByUid( $userid );


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH =>
                \Module\Profile\Avatars\toArrayResponseFromAvatarEntity($entity)
        ];
    }
}
