<?php
namespace Module\Profile\Actions;

use Module\Apanaj\Storage\HandleIrTenderBin;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoAvatars;
use Module\Profile\Model\UploadAvatarHydrate;
use Module\Profile\Model\Entity\EntityAvatar;
use Poirot\ApiClient\AccessTokenObject;
use Poirot\ApiClient\TokenProviderSolid;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\TenderBinClient\FactoryMediaObject;


class UploadAvatarAction
    extends aAction
{
//    const STORAGE_TYPE = HandleIrTenderBin::STORAGE_TYPE;
    const STORAGE_TYPE = 'tenderbin';


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

        } catch (exUnexpectedValue $e) {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }


        ## Store Image Into Object Storage
        #
        $r      = $this->_storeAvatar($avatar, $token);
        $binArr = $r['bindata'];


        ## Set Image As Avatar
        #
        $entity = $this->repoAvatars->findOneByOwnerUid( $token->getOwnerIdentifier() );
        if (! $entity ) {
            $entity = new EntityAvatar;
            $entity->setUid( $token->getOwnerIdentifier() );
        }

        if ( $avatar->getAsPrimary() )
            $entity->setPrimary( $binArr['hash'] );

        // SET_STORAGE
        $entity->addMedia(FactoryMediaObject::of([
            'storage_type' => self::STORAGE_TYPE,
            'hash'         => $binArr['hash'],
            'content_type' => $binArr['content_type'],
        ]));


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

    // ..

    function _storeAvatar(UploadAvatarHydrate $avatar, iAccessToken $token)
    {
        $storageType = self::STORAGE_TYPE;
        $handler     = FactoryMediaObject::hasHandlerOfStorage($storageType);


        $c = $handler->client();

        // Request Behalf of User as Owner With Token
        $c->setTokenProvider(new TokenProviderSolid(
            new AccessTokenObject(['access_token' => $token->getIdentifier()])
        ));

        $r = $c->store(
            fopen($avatar->getPic()->getTmpName(), 'rb')
            , null
            , $avatar->getPic()->getClientFilename()
            , [
                '_segment'         => 'avatar',
                '__before_created' => '{ "optimage": {"type": "crop", "size": "400x400", "q": 80} }',
                '__after_created'  => '{ "mime-type": {
                   "types": [
                     "image/*"
                   ],
                   "then": {
                     "versions":[{ 
                          "thumb":     {"optimage": {"type": "crop",   "size": "90x90", "q": 80}}, 
                    }]
                   }
                 }
               }',
            ]
            , null
            , false );


        return $r;
    }
}
