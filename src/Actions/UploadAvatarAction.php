<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Avatars\FactoryMediaObject;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\Entity\Avatars\aMediaObject;
use Module\Profile\Model\UploadAvatarHydrate;
use Module\Profile\Model\Entity\EntityAvatar;
use Poirot\ApiClient\AccessTokenObject;
use Poirot\ApiClient\TokenProviderSolid;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class UploadAvatarAction
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
     * Register User Profile
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

        try {
            $avatar = new UploadAvatarHydrate($this->request);

            $c = \Module\TenderBinClient\Services::ClientTender();

            // Request Behalf of User as Owner With Toke
            $c->setTokenProvider(new TokenProviderSolid(
                new AccessTokenObject(['access_token' => $token->getIdentifier()])
            ));

            $r = $c->store(
                fopen($avatar->getPic()->getTmpName(), 'rb')
                , null
                , $avatar->getPic()->getClientFilename()
                , []
                , null
                , false
            );

            $binArr = $r['bindata'];

            $entity = new EntityAvatar;
            $entity->setUid( $token->getOwnerIdentifier() );
            if ($avatar->getAsPrimary())
                $entity->setPrimary( $binArr['hash'] );

            $entity->addMedia(FactoryMediaObject::of([
                'hash'         => $binArr['hash'],
                'content_type' => $binArr['content_type'],
            ]));

        } catch (exUnexpectedValue $e) {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }


        # Persist Entity
        #
        $pEntity = $this->repoAvatars->save($entity);


        # Build Response:
        #
        return [
            ListenerDispatch::RESULT_DISPATCH =>
                \Module\Profile\Avatars\toArrayResponseFromAvatarEntity($pEntity)
        ];
    }
}
