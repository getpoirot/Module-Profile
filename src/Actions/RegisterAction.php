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

        $uid = $token->getOwnerIdentifier();

        try {

            # Create Profile Entity From Http Request
            #
            $hydrate = new ProfileHydrate(
                ProfileHydrate::parseWith($this->request)
            );

            $displayName = $hydrate->getDisplayName();
            if (empty($displayName)) {
                // Retrieve Name From OAuth
                $displayName = \Poirot\Std\catchIt(function() use ($uid) {
                    $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($uid) {
                        $info = \Module\OAuth2Client\Services::OAuthFederate()
                            ->getAccountInfoByUid($uid);

                        return $info['user']['fullname'];
                    });

                    return $nameFromOAuthServer;

                }, function () {
                    return '';
                });


                $hydrate->setDisplayName($displayName);
            }

            $entity = new EntityProfile($hydrate);
            $entity->setUid( $uid ); // Set User Who Has Own Profile!!


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
