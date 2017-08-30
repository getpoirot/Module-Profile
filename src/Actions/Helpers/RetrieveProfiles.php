<?php
namespace Module\Profile\Actions\Helpers;

use Module\HttpFoundation\Actions\Url;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Entity\EntityProfile;
use Poirot\Std\Type\StdTravers;


class RetrieveProfiles
{
    /** @var iRepoProfiles */
    protected $repoProfiles;


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
     * Retrieve Profiles For Given List Of Users By UID
     *
     * @param array  $userIds
     * @param string $mode    basic | full
     *
     * @return array
     */
    function __invoke(array $userIds, $mode = 'basic')
    {
        # Retrieve User ID From OAuth
        #
        $oauthInfos = $nameFromOAuthServer = \Poirot\Std\reTry(function () use ($userIds) {
            $infos = \Module\OAuth2Client\Services::OAuthFederate()
                ->listAccountsInfoByUIDs($userIds);

            return $infos;
        });

        /*
         * [
             [598ee6c3110f3900154718b5] => [
               [user] => [
                 [uid] => 598ee6c3110f3900154718b5
                 [fullname] => Payam Naderi
                 [username] => pnaderi
                 [email] => naderi.payam@gmail.com
                 [mobile] => [
                   [country_code] => +98
                   [number] => 9386343994
                 ]
                 [meta] => [
                   [client] => test@default.axGEceVCtGqZAdW3rc34sqbvTASSTZxD
                 ]
                 ..
               [is_valid] =>
               [is_valid_more] => [
                    [username] => 1
                    [email] =>
                    [mobile] => 1
               ]
               ..
         */
        $oauthUsers = $oauthInfos['items'];


        # Retrieve Profiles
        #
        $crsr = $this->repoProfiles->findAllByUIDs( array_keys($oauthUsers) );

        // Create map of uid => entity; used on build response
        $profiles = [];
        /** @var EntityProfile $entity */
        foreach ($crsr as $entity)
            $profiles[(string)$entity->getUid()] = $entity;


        # Build Response
        #
        $r = [];
        foreach ($oauthUsers as $oauthInfo)
        {
            $uid    = $oauthInfo['user']['uid'];
            $entity = @$profiles[ $uid ];

            $r[$uid] = [
                'uid'      => $uid,
                'fullname' => ($entity && $entity->getDisplayName()) ? $entity->getDisplayName() : $oauthInfo['user']['fullname'],
                'username' => $oauthInfo['user']['username'],
                'avatar'   => (string) \Module\HttpFoundation\Actions::url(
                    'main/profile/delegate/profile_pic'
                    , [ 'userid' => $uid ]
                    , Url::ABSOLUTE_URL | Url::DEFAULT_INSTRUCT | Url::ENCODE_URL
                ),
                'privacy_stat' => ($entity && $entity->getPrivacyStatus())
                    ? $entity->getPrivacyStatus() : EntityProfile::PRIVACY_PUBLIC
            ];
        }

        return $r;
    }
}
