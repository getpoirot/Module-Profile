<?php
namespace Module\Profile\Actions\Helpers;

use Module\HttpFoundation\Actions\Url;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use Module\Profile\Module;


class IsUserTrusted
{
    /** @var iRepoProfiles */
    protected $repoProfiles;

    protected $trustedUser=null;


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
    function __invoke($userId=null)
    {

            $trustedUser  =$this->getTrustedUser();

        $trusted= in_array($userId,$trustedUser);

        return $trusted;
    }


    protected function getTrustedUser()
    {
        if (is_null($this->trustedUser))
             return $this->trustedUser  = \Module\Foundation\Actions::config(\Module\Profile\Module::CONF, 'trusted');


    }
}
