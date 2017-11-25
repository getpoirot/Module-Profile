<?php
namespace Module\Profile\Actions\Helpers;

use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;


class IsUserTrusted
{
    /** @var iRepoProfiles */
    protected $repoProfiles;

    protected $trustedUsers;


    /**
     * Construct
     *
     * @param iRepoProfiles $repoProfiles @IoC /module/profile/services/repository/Profiles
     */
    function __construct(iRepoProfiles $repoProfiles)
    {
        $this->repoProfiles = $repoProfiles;
    }


    /**
     * check user is trusted or not
     *
     * @param mixed  $userId
     *
     * @return boolean
     */
    function __invoke($userId = null)
    {
        $trustedUser = $this->_getTrustedUsers();
        return in_array( (string) $userId, $trustedUser);
    }


    // ..

    protected function _getTrustedUsers()
    {
        if ( null === $this->trustedUsers )
             $this->trustedUsers  = \Module\Foundation\Actions::config(\Module\Profile\Module::CONF, 'trusted');

        return $this->trustedUsers;
    }
}
