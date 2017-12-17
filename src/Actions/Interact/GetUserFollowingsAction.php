<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetUserFollowingsAction
    extends aAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = [

    ];

    /** @var iRepoFollows */
    protected $repoFollows;
    /** @var iRepoProfiles */
    protected $repoProfiles;


    /**
     * Construct
     *
     * @param iHttpRequest $httpRequest   @IoC /HttpRequest
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     */
    function __construct(iHttpRequest $httpRequest, iRepoFollows $repoFollows, iRepoProfiles $repoProfiles)
    {
        parent::__construct($httpRequest);

        $this->repoFollows  = $repoFollows;
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

        }


        // TODO check users interaction privacy

        #
        $q       = ParseRequestData::_($this->request)->parseQueryParams();
        $limit   = isset($q['limit']) ? $q['limit'] : 10;
        $offset  = isset($q['offset']) ? $q['offset'] : null;


        # List Whole Followers
        #
        $followers = $this->repoFollows->findAllForOutgoings(
            $userid
            , [
                'stat' => EntityFollow::STAT_ACCEPTED
            ]
            , $limit+1
            , $offset
            , iRepoFollows::SORT_DESC
        );


        # Build Response
        #
        $nextOffset=null;

        $r = []; $c = 0;
        /** @var EntityFollow $f */
        foreach ($followers as $f) {
            $nextOffset = (string)$f->getUid();
            $r[ (string) $f->getIncoming() ] = [
                'request_id' => (string) $f->getUid(),
                'created_on' => [
                    'datetime'  => $f->getDateTimeCreated(),
                    'timestamp' => $f->getDateTimeCreated()->getTimestamp(),
                ],
                'user' => [
                    /*
                    'uid'          => (string) $f->getOutgoing(),
                    'fullname'     => 'Payam Naderi',
                    'username'     => 'e1101',
                    'avatar'       => 'http://localhost:80/profile/-5997f286c39962003b255ba2/profile.jpg',
                    'privacy_stat' => 'public',
                    */
                ],
            ];

            $c++;
        }


        # Retrieve Users Account Info
        #
        if (! empty($r) ) {
            $profiles = \Module\Profile\Actions::RetrieveProfiles(array_keys($r));

            foreach ($r as $uid => $rq) {
                if (! isset($profiles[$uid]) ) {
                    unset($r[$uid]);
                    continue;
                }

                $r[$uid]['user'] = $profiles[$uid];
            }
        }


        ## Build Link_more
        #
        $linkMore = null;
        if ($c > $limit) {
            array_pop($r);

            $linkMore   = \Module\HttpFoundation\Actions::url(null);
            $linkMore   = (string) $linkMore->uri()->withQuery('offset='.($nextOffset).'&limit='.$limit);
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => count($r),
                'items' => array_values($r),
                '_link_more' => $linkMore,
                '_self' => [
                    'offset' => $offset,
                    'limit'  => $limit,
                ],
            ]
        ];
    }
}
