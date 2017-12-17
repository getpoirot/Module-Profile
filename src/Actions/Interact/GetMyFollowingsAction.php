<?php
namespace Module\Profile\Actions\Interact;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Actions\aAction;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Model\Entity\EntityFollow;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class GetMyFollowingsAction
    extends aAction
{
    /** @var iRepoFollows */
    protected $repoFollows;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     */
    function __construct(iHttpRequest $httpRequest, iRepoFollows $repoFollows)
    {
        parent::__construct($httpRequest);

        $this->repoFollows  = $repoFollows;
    }


    /**
     * Retrieve Followers
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


        $q       = ParseRequestData::_($this->request)->parseQueryParams();
        $limit   = isset($q['limit'])  ? $q['limit']  : 30;
        $offset  = isset($q['offset']) ? $q['offset'] : null;


        # List Whole Followers
        #
        $followers = $this->repoFollows->findAllForOutgoings(
            $token->getOwnerIdentifier()
            , [
                'stat' => EntityFollow::STAT_ACCEPTED
            ]
            , $limit+1
            , $offset
            , iRepoFollows::SORT_DESC
        );


        # Build Response
        #
        $r = []; $c = 0;
        $nextOffset = null;
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
                'count'      => count($r),
                'items'      => array_values($r),
                '_link_more' => $linkMore,
                '_self' => [
                    'offset' => $offset,
                    'limit'  => $limit,
                ],
            ],
        ];
    }
}
