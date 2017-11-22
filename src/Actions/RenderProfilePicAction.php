<?php
namespace Module\Profile\Actions;

use Poirot\Http\HttpResponse;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Http\Header\FactoryHttpHeader;
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
        if ( $r['primary'] ) {

            // Redirect To Object-Storage Url Of Media
            // http://optimizer.app-tech.co/?type=crop&size=75x75&url=storage.app-tech.co/bin/59e1f758eddb9e0a49327603/file.jpg
            // TODO Dirty fix; remove from optimizer
//            $link = 'http://optimizer.'.SERVER_NAME.'/?type=crop&size=200x200&url='.$r['primary']['_link']['origin'].'/file.jpg';
            $link = $r['primary']['_link']['thumb'];
        }
        else
            // Default None-Profile Picture
            // TODO Configurable with merged config
            $link = 'http://'.SERVER_NAME.'/release/no_avatar.jpg';


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

        /*
        header('Content-Type: image/jpeg');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo file_get_contents($link);
        die;
        */
    }
}
