<?php
namespace Module\Profile
{
    use Module\Profile\Model\Entity\EntityProfile;


    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityProfile $profile
     *
     * @return array
     */
    function toArrayResponseFromProfileEntity(EntityProfile $profile)
    {
        return [
            'profile' => [
                'uid'        => (string) $profile->getUid(),
                'display_name' => (string) $profile->getDisplayName(),
                'bio'        => (string) $profile->getBio(),
                'gender'     => (string) $profile->getGender(),
                'location'   => ($profile->getLocation()) ? [
                    'caption' => $profile->getLocation()->getCaption(),
                    'geo'     => [
                        'lon' => $profile->getLocation()->getGeo('lon'),
                        'lat' => $profile->getLocation()->getGeo('lat'),
                    ],
                ] : null,
                'birthday' => ($profile->getBirthday()) ? [
                    'datetime'  => $profile->getBirthday(),
                    'timestamp' => $profile->getBirthday()->getTimestamp(),
                ] : null,
                'datetime_created' => [
                    'datetime'  => $profile->getDateTimeCreated(),
                    'timestamp' => $profile->getDateTimeCreated()->getTimestamp(),
                ],
            ],
        ];
    }

}

namespace Module\Profile\Avatars
{
    use Module\Profile\Model\Entity\EntityAvatar;
    use Poirot\TenderBinClient\Model\aMediaObject;


    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityAvatar $avatars
     *
     * @return array
     */
    function toArrayResponseFromAvatarEntity(EntityAvatar $avatars = null)
    {
        if (null === $avatars) {
            $p = null;
            $r = [];

        } else {
            $r = \Poirot\TenderBinClient\embedLinkToMediaData( $avatars->getMedias() );
            $p = current($r); // first as primary profile pic
            /** @var aMediaObject $m */
            foreach ($r as $m) {
                if ($m['hash'] !== $avatars->getPrimary())
                    continue;

                $p = $m;
            }
        }

        return [
            'primary' => $p,
            'medias'  => $r,
        ];
    }

}