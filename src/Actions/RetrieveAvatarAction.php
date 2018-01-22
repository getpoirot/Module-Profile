<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RetrieveAvatarAction
    extends aAction
{
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
     * Retrieve avatar
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


        # Retrieve Avatars For User
        #
        $entity = $this->repoAvatars->findOneByOwnerUid( $token->getOwnerIdentifier() );


        \Module\Profile\Avatars\assertPrimaryOnAvatarEntity($entity);


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH =>
                \Module\Profile\Avatars\toArrayResponseFromAvatarEntity($entity)
        ];
    }
}
