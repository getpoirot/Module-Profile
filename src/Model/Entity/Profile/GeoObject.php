<?php
namespace Module\Profile\Model\Entity\Profile;

use Poirot\Std\Struct\aValueObject;


class GeoObject
    extends aValueObject
{
    /** @var array [lon, lat] */
    protected $geo;
    /** @var string Geo Lookup Caption  */
    protected $caption;


    /**
     * Set GeoLocation
     *
     * the first field should contain the longitude value and the second
     * field should contain the latitude value.
     *
     * [ "lon": 28.5122077,
     *   "lat": 53.5818702 ]
     * or
     * [ 28.5122077, 53.5818702 ]
     *
     * @param array [lon, lat] $location
     *
     * @return $this
     */
    function setGeo($location)
    {
        if (isset($location['lon']))
            $this->geo = array($location['lon'], $location['lat']);
        else
            $this->geo = array($location[0], $location[1]);

        return $this;
    }

    /**
     * Get Geo Location
     *
     * @param string|null $lonLat 'lat'|'lon'
     *
     * @return array|null
     * @throws \Exception
     */
    function getGeo($lonLat = null)
    {
        if ($lonLat === null)
            return $this->geo;

        if (!$this->geo)
            // No Geo Data Provided
            return null;

        # Geo Property (lon, lat)
        $lonLat = strtolower( (string) $lonLat );
        switch ($lonLat) {
            case 'lon':
            case 'long':
            case 'longitude':
                return $this->geo[0];
            case 'lat':
            case 'latitude':
                return $this->geo[1];
            default:
                throw new \Exception(sprintf('Unknown Geo Property (%s).', $lonLat));
        }
    }

    /**
     * Set Geo Lookup Caption
     *
     * @param string $entitle
     *
     * @return $this
     */
    function setCaption($entitle)
    {
        $this->caption = (string) $entitle;
        return $this;
    }

    /**
     * Get Geo Lookup Caption
     *
     * @return string
     */
    function getCaption()
    {
        return $this->caption;
    }
}
