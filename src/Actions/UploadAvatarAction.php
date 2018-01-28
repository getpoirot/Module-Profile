<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Events\EventsHeapOfProfile;
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

        // TODO add subversions into entity persistence
        // SET_STORAGE
        $entity->addMedia(FactoryMediaObject::of([
            'hash'         => $binArr['hash'],
            'content_type' => $binArr['content_type'],
            'meta'         => $binArr['meta']
        ]));


        ## Assert For Primary
        #
        \Module\Profile\Avatars\assertPrimaryOnAvatarEntity($entity);


        # Persist Entity
        #
        $pEntity = $this->repoAvatars->save($entity);


        ## Event
        #
        $this->event()
            ->trigger(EventsHeapOfProfile::AVATAR_UPLOADED, [
                'entity_avatar' => $pEntity
            ])
        ;


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
        $handler = FactoryMediaObject::getDefaultHandler();


        $c = $handler->client();

        // Request Behalf of User as Owner With Token
        $c->setTokenProvider(new TokenProviderSolid(
            new AccessTokenObject(['access_token' => $token->getIdentifier()])
        ));

        $r = $c->store(
            $avatar->getPic()
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
                          "thumb":     {"optimage": {"type": "crop",   "size": "90x90", "q": 80}}
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
