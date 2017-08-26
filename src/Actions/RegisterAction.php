<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use Module\Profile\Model\ProfileHydrate;
use Module\Profile\Model\ProfileValidate;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class RegisterAction
    extends aAction
{
    /** @var iRepoProfiles */
    protected $repoProfiles;


    /**
     * Construct
     *
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     */
    function __construct(iHttpRequest $httpRequest, iRepoProfiles $repoProfiles)
    {
        parent::__construct($httpRequest);

        $this->repoProfiles = $repoProfiles;
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

            # Create Profile Entity From Http Request
            #
            $hydrate = new ProfileHydrate(
                ProfileHydrate::parseWith($this->request)
            );


            $entity = new EntityProfile($hydrate);
            $entity->setUid( $token->getOwnerIdentifier() ); // Set User Who Has Own Profile!!


            // TODO Inject display name from oauth server if not given


            __(new ProfileValidate($entity))
                ->assertValidate();

        } catch (exUnexpectedValue $e) {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);

        } catch (\Exception $e) {
            throw $e;
        }


        # Persist Profile
        #
        $profile = $this->repoProfiles->save($entity);


        # Build Response
        #
        return [
            ListenerDispatch::RESULT_DISPATCH => \Module\Profile\toArrayResponseFromProfileEntity(
                $profile
            ),
        ];
    }
}
