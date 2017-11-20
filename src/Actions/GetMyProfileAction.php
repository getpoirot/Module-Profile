<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetMyProfileAction
    extends aAction
{
    protected $tokenMustHaveOwner  = true;
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
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        $userid = $token->getOwnerIdentifier();

        // Retrieve User ID From OAuth
        $oauthInfo = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($userid) {
            $info = \Module\OAuth2Client\Services::OAuthFederate()
                ->getAccountInfoByUid($userid);

            return $info;
        });


        # Retrieve Avatars For User
        #
        $entity = $this->repoProfiles->findOneByUID( $userid );


        # Build Response
        #
        $r = [
            'uid'      => $oauthInfo['user']['uid'],
            'fullname' => ($entity && $entity->getDisplayName()) ? $entity->getDisplayName() : $oauthInfo['user']['fullname'],
            'username' => $oauthInfo['user']['username'],
            'mobile'   => $oauthInfo['user']['mobile'],
            'avatar'   => (string) \Module\HttpFoundation\Actions::url(
                'main/profile/delegate/profile_pic'
                , [ 'userid' => $oauthInfo['user']['uid'] ]
                , Url::ABSOLUTE_URL | Url::DEFAULT_INSTRUCT
            ),
            'personal' => ($entity) ? [
                'bio'      => (string) $entity->getBio(),
                'gender'   => (string) $entity->getGender(),
                'location'   => ($entity->getLocation()) ? [
                    'caption' => $entity->getLocation()->getCaption(),
                    'geo'     => [
                        'lon' => $entity->getLocation()->getGeo('lon'),
                        'lat' => $entity->getLocation()->getGeo('lat'),
                    ],
                ] : null, // TODO With privacy interaction
                'birthday' => ($entity->getBirthday()) ? [
                    'datetime'  => $entity->getBirthday(),
                    'timestamp' => $entity->getBirthday()->getTimestamp(),
                ] : null,
            ] : null,
            'privacy_stat' => ($entity && $entity->getPrivacyStatus())
                ? $entity->getPrivacyStatus() : EntityProfile::PRIVACY_PUBLIC,

            'trusted'      => \Module\Profile\Actions::IsUserTrusted($oauthInfo['user']['uid']),

            'is_valid' => $oauthInfo['is_valid'],
            'is_valid_more' => $oauthInfo['is_valid_more'],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
