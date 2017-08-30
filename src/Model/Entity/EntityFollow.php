<?php
namespace Module\Profile\Model\Entity;

use Poirot\Std\Struct\aDataOptions;


class EntityFollow
    extends aDataOptions
{
    const STAT_PENDING  = 'pending';
    const STAT_REJECTED = 'rejected';
    const STAT_ACCEPTED = 'accepted';

    protected $uid;
    protected $outgoing;
    protected $incoming;
    protected $stat;
    protected $datetimeCreated;
    protected $datetimeUpdated;


    /**
     * @param $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get Unique Identifier
     *
     * @ignore
     */
    function getUid()
    {
        return $this->uid;
    }

    /**
     * Set Requester(Outgoing) Unique Identifier
     *
     * @param mixed $outgoing
     *
     * @return $this
     */
    function setOutgoing($outgoing)
    {
        $this->outgoing = $outgoing;
        return $this;
    }

    /**
     * Get Requester(Outgoing) Unique Identifier
     *
     * @return mixed
     */
    function getOutgoing()
    {
        return $this->outgoing;
    }

    /**
     * Set Receiver(Outgoing) Unique Identifier
     *
     * @param mixed $incoming
     *
     * @return $this
     */
    function setIncoming($incoming)
    {
        $this->incoming = $incoming;
        return $this;
    }

    /**
     * Get Receiver(Outgoing) Unique Identifier
     *
     * @return mixed
     */
    function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * Set Stat Of Request Follow
     *
     * @param string $stat
     *
     * @return $this
     */
    function setStat($stat)
    {
        $this->stat = (string) $stat;
        return $this;
    }

    /**
     * Get Request Follow Stat
     *
     * @return string
     */
    function getStat()
    {
        return $this->stat;
    }

    /**
     * Set Created Timestamp
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setDateTimeCreated($dateTime)
    {
        if ( !($dateTime === null || $dateTime instanceof \DateTime) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of \Datetime or null; given: (%s).'
                , \Poirot\Std\flatten($dateTime)
            ));


        $this->datetimeCreated = $dateTime;
        return $this;
    }

    /**
     * Get Date Time Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        if (! $this->datetimeCreated )
            $this->setDateTimeCreated( new \DateTime );

        return $this->datetimeCreated;
    }

    /**
     * Set Updated Timestamp
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setDateTimeUpdated($dateTime)
    {
        if ( !($dateTime === null || $dateTime instanceof \DateTime) )
            throw new \InvalidArgumentException(sprintf(
                'Datetime must instance of \Datetime or null; given: (%s).'
                , \Poirot\Std\flatten($dateTime)
            ));


        $this->datetimeUpdated = $dateTime;
        return $this;
    }

    /**
     * Get Date Time Updated
     *
     * @return \DateTime|null
     */
    function getDateTimeUpdated()
    {
        return $this->datetimeUpdated;
    }
}
