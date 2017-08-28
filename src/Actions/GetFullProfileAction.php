<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetFullProfileAction
    extends aAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = [

    ];

    /** @var iRepoProfiles */
    protected $repoProfiles;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest   @IoC /HttpRequest
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     */
    function __construct(iHttpRequest $httpRequest, iRepoProfiles $repoProfiles)
    {
        parent::__construct($httpRequest);

        $this->repoProfiles = $repoProfiles;
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
            // Retrieve User Info From OAuth By username
            $oauthInfo = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($username) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUsername($username);

                return $info;
            });

            $userid = $oauthInfo['user']['uid'];

        } else {
            // Retrieve User ID From OAuth
            $oauthInfo = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($userid) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUid($userid);

                return $info;
            });
        }


        # Retrieve Avatars For User
        #
        $entity = $this->repoProfiles->findOneByUID( $userid );
        if (! $entity )
            throw new exRouteNotMatch('User Not Found.');


        # Build Response
        #
        $r = [
            'uid'      => $oauthInfo['user']['uid'],
            'fullname' => ($entity && $entity->getDisplayName()) ? $entity->getDisplayName() : $oauthInfo['user']['fullname'],
            'username' => $oauthInfo['user']['username'],
            'avatar'   => (string) \Module\HttpFoundation\Actions::url(
                'main/profile/delegate/profile_pic'
                , [ 'userid' => $oauthInfo['user']['uid'] ]
                , Url::ABSOLUTE_URL | Url::DEFAULT_INSTRUCT
            ),
            'personal' => [
                'bio'      => (string) $entity->getBio(),
                'gender'   => (string) $entity->getGender(),
                'location' => $entity->getLocation(),
                'birthday' => [
                    'datetime'  => $entity->getBirthday(),
                    'timestamp' => $entity->getBirthday()->getTimestamp(),
                ],
            ],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
