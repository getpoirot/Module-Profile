<?php
namespace Module\Profile
{

    use Module\Profile\Model\Entity\Avatars\MediaObjectTenderBin;
    use Module\Profile\Model\Entity\EntityProfile;
    use Poirot\Std\Type\StdArray;
    use Poirot\Std\Type\StdTravers;

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

    /**
     * Embed Retrieval Http Link To MediaObjects
     *
     * @param array|\Traversable $content Content include MediaObject
     *
     * @return array Prepared content include link(s)
     */
    function embedLinkToMediaAvatars($content)
    {
        if ($content instanceof \Traversable )
            $content = StdTravers::of($content)->toArray();

        if (! is_array($content) )
            return $content;


        $content = StdArray::of($content)->withWalk(function(&$val) {
            if ($val instanceof MediaObjectTenderBin) {
                $orig         = $val;
                $val          = StdTravers::of($val)->toArray();
                $val['_link'] = (string) \Module\Foundation\Actions::Path(
                    'mod-content-media_cdn' // this name is reserved; @see mod-content.conf.php
                    , [
                        'hash' => $orig->getHash()
                    ]
                );
            }
        });

        return $content->value; // instance access to internal array
    }
}

namespace Module\Profile\Avatars
{
    use Module\Profile\Model\Entity\Avatars\aMediaObject;
    use Module\Profile\Model\Entity\Avatars\MediaObjectTenderBin;
    use Poirot\Std\Interfaces\Pact\ipFactory;


    class FactoryMediaObject
        implements ipFactory
    {
        /**
         * Factory With Valuable Parameter
         *
         * @param null  $mediaData
         *
         * @return aMediaObject
         * @throws \Exception
         */
        static function of($mediaData = null)
        {
            // Content Object May Fetch From DB Or Sent By Post Http Request

            /*
            {
                "storage_type": "tenderbin",
                "hash": "58c7dcb239288f0012569ed0",
                "content_type": "image/jpeg",
                "as_primary": "true",
            }
            */

            if ($mediaData instanceof \Traversable)
                $mediaData = \Poirot\Std\cast($mediaData)->toArray();
            elseif ($mediaData instanceof \stdClass)
                $mediaData = \Poirot\Std\toArrayObject($mediaData);


            if (! isset($mediaData['storage_type']) )
                $mediaData['storage_type'] = 'tenderbin';

            switch (strtolower($mediaData['storage_type'])) {
                case 'tenderbin':
                    if (isset($mediaData['as_primary']))
                        $mediaData['as_primary'] = filter_var($mediaData['as_primary'], FILTER_VALIDATE_BOOLEAN);

                    $objectMedia = new MediaObjectTenderBin;
                    $objectMedia->with( $objectMedia::parseWith($mediaData) );
                    break;

                default:
                    throw new \Exception('Object Storage With Name (%s) Is Unknown.');
            }

            return $objectMedia;
        }
    }
}