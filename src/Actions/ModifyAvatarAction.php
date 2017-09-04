<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\Entity\Avatars\aMediaObject;
use Module\Profile\Model\UploadAvatarHydrate;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


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

        if ( $entity ) {
            $hydrate = new UploadAvatarHydrate($this->request);
            if ( $hydrate->getAsPrimary() )
            {
                /** @var aMediaObject $m */
                foreach ($entity->getMedias() as $m) {
                    // Check whether given hash_id is belong to the media list

                    if ($m->getHash() == $hash_id) {
                        $entity->setPrimary($hash_id);
                        $this->repoAvatars->save($entity);
                        break;
                    }
                }
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
