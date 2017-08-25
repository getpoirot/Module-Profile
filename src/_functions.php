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
