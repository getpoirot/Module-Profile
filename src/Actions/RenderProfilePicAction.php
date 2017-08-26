<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RenderProfilePicAction
    extends aAction
{
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
        $r      = \Module\Profile\Avatars\toArrayResponseFromAvatarEntity($entity);


        # Build Avatar Link
        #
        if ( $r['primary'] )
            // Redirect To Object-Storage Url Of Media
            $link = $r['primary']['_link'];
        else
            // Default None-Profile Picture
            // TODO Configurable with merged config
            $link = 'http://app-tech.co/release/no_avatar.jpg';


        # Build Response
        #
        $response = new HttpResponse;
        $response->setStatusCode(301); // permanently moved
        $response->headers()->insert(FactoryHttpHeader::of([
            'Location' => $link,
        ]));

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
