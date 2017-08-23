<?php
namespace Module\Profile\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use Module\Profile\Model\HydrateEntityProfile;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


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
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Create Profile Entity From Http Request
        #
        $hydrate = new HydrateEntityProfile(
            HydrateEntityProfile::parseWith($this->request)
        );

        $entity = new EntityProfile($hydrate);
        $entity->setUid( $token->getOwnerIdentifier() ); // Set User Who Has Own Profile!!


        // TODO Validate Entity
        

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
