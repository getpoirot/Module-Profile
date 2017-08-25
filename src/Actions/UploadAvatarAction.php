<?php
namespace Module\Profile\Actions;

use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


class UploadAvatarAction
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


        
        die('>_');
    }
}
