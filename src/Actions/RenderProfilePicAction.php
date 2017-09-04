<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class RenderProfilePicAction
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
            $userid = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($username) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUsername($username);

                return $info['user']['uid'];
            });
        }


        # Retrieve Avatars For User
        #
        $entity = $this->repoAvatars->findOneByOwnerUid( $userid );
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
        $response->headers()
            ->insert(FactoryHttpHeader::of(['Location' => $link, ]))
            ->insert(FactoryHttpHeader::of(['Cache-Control' => 'no-cache, no-store, must-revalidate',]))
            ->insert(FactoryHttpHeader::of(['Pragma' => 'no-cache',]))
            ->insert(FactoryHttpHeader::of(['Expires' => '0',]))
        ;

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
