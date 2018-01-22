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
     * Assert Primary For Entity
     *
     * @param EntityAvatar $avatars
     */
    function assertPrimaryOnAvatarEntity(EntityAvatar $avatars)
    {
        $primary = $avatars->getPrimary();


        /** @var aMediaObject $m */
        $found = false;
        foreach ($avatars->getMedias() as $m) {
            if (! isset($first) )
                // keep first media as primary
                $first = $m->getHash();

            if ($m->getHash() == $primary)
                $found |= true;
        }


        // ORDER IS MANDATORY

        if ($primary && !$found)
            // Media Object Associated With Primary Hash No Longer Exists
            $primary = null;


        if (! $primary && isset($first) )
            // primary not given we choose first!!
            $primary = $first;


        $avatars->setPrimary($primary);
    }


    /**
     * Build Array Response From Given Entity Object
     *
     * @param EntityAvatar $avatars
     *
     * @return array
     */
    function toArrayResponseFromAvatarEntity(EntityAvatar $avatars = null)
    {
        $medias = ($avatars !== null) ? $avatars->getMedias() : [];

        if ( null === $avatars || empty($medias) ) {
            $p = null;
            $r = [];

        } else {
            /*
             * [
             *   [
                    [storage_type] => tenderbin
                    [hash] => 59eda4e595a8c1035460b282
                    [content_type] => image/jpeg
                    [_link] => http://storage.apanajapp.com/bin/59eda4e595a8c1035460b282
                 ]
                 ...
               ]
             */

            ## Embed Versions Into Response
            #
            $r = \Poirot\TenderBinClient\embedLinkToMediaData(
                $medias
                , function($m) {
                    $link = $m['_link'];
                    $m['_link'] = [
                        'origin' => $link,
    //                    'thumb'  => 'http://optimizer.'.SERVER_NAME.'/?type=crop&size=400x400&url='.$link.'/file.jpg',
                        'thumb'  => $link.'?ver=thumb',
                    ];

                    return $m;
                }
            );


            $r = array_reverse($r);

            $p = current($r); // first as primary profile pic
            /** @var aMediaObject $m */
            $j = 0;
            foreach ($r as $i => $m) {
                if ( $m['hash'] !== $avatars->getPrimary() )
                    continue;

                unset($r[$i]);
                $p = $m;
                $j++;
            }

            if ($j > 0)
                array_unshift($r, $p);
        }


        return [
            'primary' => $p,
            'medias'  => $r,
        ];
    }
}
