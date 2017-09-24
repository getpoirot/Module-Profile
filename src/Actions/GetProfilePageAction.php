<?php
namespace Module\Profile\Actions;

use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityFollow;
use Module\Profile\Model\Entity\EntityProfile;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetProfilePageAction
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
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     * @param iRepoPosts    $repoPosts    @IoC /module/content/services/repository/Posts
     */
    function __construct(iHttpRequest $httpRequest, iRepoProfiles $repoProfiles, iRepoFollows $repoFollows, iRepoPosts $repoPosts)
    {
        parent::__construct($httpRequest);

        $this->repoProfiles = $repoProfiles;
        $this->repoFollows  = $repoFollows;
        $this->repoPosts = $repoPosts;
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
            $oauthInfo = \Poirot\Std\reTry(function () use ($username) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUsername($username);

                return $info;
            });

            $userid = $oauthInfo['user']['uid'];

        } else {
            // Retrieve User ID From OAuth
            $oauthInfo = \Poirot\Std\reTry(function () use ($userid) {
                $info = \Module\OAuth2Client\Services::OAuthFederate()
                    ->getAccountInfoByUid($userid);

                return $info;
            });
        }


        # Retrieve Avatars For User
        #
        $entity = $this->repoProfiles->findOneByUID( $userid );
        if (! $entity )
            throw new exRouteNotMatch(sprintf(
                'User %s not exists.'
                , $userid
            ));


        # Find Relation Between Users
        #
        $visitor = $token->getOwnerIdentifier();

        if ($visitor == $userid) {
            // You visit Yourself!!
            $relation = 'self';
        } else {
            // outward
            $outward = 'none';
            if ( $fe = $this->repoFollows->findOneWithInteraction($visitor, $userid) ) {
                // only stat of pending and accepted are allows
                $stat = $fe->getStat();
                if (in_array($stat, [EntityFollow::STAT_ACCEPTED, EntityFollow::STAT_PENDING]))
                    $outward = $stat;
            }

            // inward
            $inward = 'none';
            if ( $fe = $this->repoFollows->findOneWithInteraction($userid, $visitor) ) {
                // only stat of pending and accepted are allows
                $stat = $fe->getStat();
                if (in_array($stat, [EntityFollow::STAT_ACCEPTED, EntityFollow::STAT_PENDING]))
                    $inward = $stat;
            }
        }


        # Count Statistics
        #
        // TODO Some data must inject with events attached to this action
        $cntFollowers  = $this->repoFollows->getCountAllForIncoming($userid, [EntityFollow::STAT_ACCEPTED]);
        $cntFollowings = $this->repoFollows->getCountAllForOutgoing($userid, [EntityFollow::STAT_ACCEPTED]);
        $cntPosts      = $this->repoPosts->getCountMatchWithOwnerId($userid);


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
            'privacy_stat' => ($entity && $entity->getPrivacyStatus())
                ? $entity->getPrivacyStatus() : EntityProfile::PRIVACY_PUBLIC,
            'relation' => (isset($relation)) ? $relation : [
                'outward' => $outward,
                'inward'  => $inward,
            ],
            'followers_count'  => $cntFollowers,
            'followings_count' => $cntFollowings,
            'posts_count'      => $cntPosts,
            'score' => 0, // TODO
            'profile' => [
                'bio'      => (string) $entity->getBio(),
                'gender'   => (string) $entity->getGender(),
                'personal' => ($entity) ? [
                    'location'   => ($entity->getLocation()) ? [
                        'caption' => $entity->getLocation()->getCaption(),
                        'geo'     => [
                            'lon' => $entity->getLocation()->getGeo('lon'),
                            'lat' => $entity->getLocation()->getGeo('lat'),
                        ],
                    ] : null, // TODO With privacy interaction
                    'birthday' => [ // TODO with privacy interaction
                        'datetime'  => $entity->getBirthday(),
                        'timestamp' => $entity->getBirthday()->getTimestamp(),
                    ],
                ] : null,
            ],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
