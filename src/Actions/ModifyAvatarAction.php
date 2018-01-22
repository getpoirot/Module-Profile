<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Events\EventsHeapOfProfile;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\UploadAvatarHydrate;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\TenderBinClient\Model\aMediaObject;


class ModifyAvatarAction
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


        # Modify Avatar From Repository
        #
        $entity  = $this->repoAvatars->findOneByOwnerUid( $token->getOwnerIdentifier() );

        if (! $entity )
            throw new exRouteNotMatch;


        $hydrate = new UploadAvatarHydrate($this->request);
        if (! $hydrate->getAsPrimary() )
            throw exUnexpectedValue::paramIsRequired('as_primary');


        $primary = $hash_id;

        if ( $primary !== $entity->getPrimary() ) {
            $entity->setPrimary($primary);

            \Module\Profile\Avatars\assertPrimaryOnAvatarEntity($entity);
            if ($entity->getPrimary() == $primary)
            {
                // save to persistence if has changed!!
                $pEntity = $this->repoAvatars->save($entity);

                ## Event
                #
                $this->event()
                    ->trigger(EventsHeapOfProfile::AVATAR_UPLOADED, [
                        'entity_avatar' => $pEntity
                    ])
                ;

            }
        }


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
