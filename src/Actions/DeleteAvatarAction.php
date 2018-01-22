<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Events\EventsHeapOfProfile;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class DeleteAvatarAction
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
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($hash_id = null, $token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Remove Avatar From Repository
        #
        $pEntity = $this->repoAvatars->delUserAvatarByHash($token->getOwnerIdentifier(), $hash_id);


        ## Assert For Primary
        #
        \Module\Profile\Avatars\assertPrimaryOnAvatarEntity($pEntity);


        ## Event
        #
        $this->event()
            ->trigger(EventsHeapOfProfile::AVATAR_UPLOADED, [
                'entity_avatar' => $pEntity
            ])
        ;


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH => [
                '_self' => [
                    'hash_id' => $hash_id,
                ],
            ],
        ];
    }
}
