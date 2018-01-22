<?php
namespace Module\Profile\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Events\EventsHeapOfProfile;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityFollow;
use Module\Profile\Model\Entity\EntityProfile;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\TenderBinClient\FactoryMediaObject;


class GetMyProfileAction
    extends aAction
{
    protected $tokenMustHaveOwner  = true;
    protected $tokenMustHaveScopes = [

    ];

    /** @var iRepoProfiles */
    protected $repoProfiles;
    /** @var iRepoFollows */
    protected $repoFollows;
    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest   @IoC /HttpRequest
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     * @param iRepoPosts    $repoPosts    @IoC /module/content/services/repository/Posts
     */
    function __construct(
        iHttpRequest $httpRequest
        , iRepoProfiles $repoProfiles
        , iRepoFollows $repoFollows
        , iRepoPosts $repoPosts
    )
    {
        parent::__construct($httpRequest);

        $this->repoProfiles = $repoProfiles;
        $this->repoFollows  = $repoFollows;
        $this->repoPosts    = $repoPosts;
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


        # Count Statistics
        #
        // TODO Some data must inject with events attached to this action
        $cntFollowers  = $this->repoFollows->getCountAllForIncoming($userid, [EntityFollow::STAT_ACCEPTED]);
        $cntFollowings = $this->repoFollows->getCountAllForOutgoing($userid, [EntityFollow::STAT_ACCEPTED]);
        $cntPosts      = $this->repoPosts->getCountMatchWithOwnerId($userid);


        # Build Response
        #

        $profile = ($entity) ? [
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
        ] : null;


        $r = [
            'uid'      => $oauthInfo['user']['uid'],
            'fullname' => ($entity && $entity->getDisplayName()) ? $entity->getDisplayName() : $oauthInfo['user']['fullname'],
            'username' => $oauthInfo['user']['username'],
            'mobile'   => $oauthInfo['user']['mobile'],
            'avatar'   => ($entity && $entity->getPrimaryAvatar())
                ? ($avatar = FactoryMediaObject::of( $entity->getPrimaryAvatar() )->get_Link().'/profile.jpg' )
                : (string) \Module\HttpFoundation\Actions::url(
                    'main/profile/delegate/profile_pic'
                    , [ 'userid' => $oauthInfo['user']['uid'] ]
                    , Url::ABSOLUTE_URL | Url::DEFAULT_INSTRUCT
            ),
            'followers_count'  => $cntFollowers,
            'followings_count' => $cntFollowings,
            'posts_count'      => $cntPosts,
            'score' => 0, // TODO

            'profile'  => $profile,
            'personal' => $profile, // backward compatibility

            'privacy_stat' => ($entity && $entity->getPrivacyStatus())
                ? $entity->getPrivacyStatus() : EntityProfile::PRIVACY_PUBLIC,

            'trusted'      => \Module\Profile\Actions::IsUserTrusted($oauthInfo['user']['uid']),

            'is_valid' => $oauthInfo['is_valid'],
            'is_valid_more' => $oauthInfo['is_valid_more'],
        ];


        ## Event
        #
        $r = $this->event()
            ->trigger(EventsHeapOfProfile::RETRIEVE_PROFILE_RESULT, [
                /** @see Profile\Events\ */
                'result' => $r, 'userid' => $userid, 'entity_profile' => $entity, 'visitor' => $userid,
            ])
            ->then(function ($collector) {
                /** @var \Module\Profile\Events\DataCollector $collector */
                return $collector->getResult();
            });


        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
