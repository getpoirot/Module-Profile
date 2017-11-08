<?php
namespace Module\Profile\Actions\Cron;

use Module\HttpFoundation\Actions\Url;
use Module\Profile\Interfaces\Model\Repo\iRepoFollows;
use Module\Profile\Interfaces\Model\Repo\iRepoProfiles;
use Module\Profile\Model\Driver\Mongo\EntityFollow;
use Module\Profile\Model\Entity\EntityProfile;


class CronCleanupRequestsAction
{
    /** @var iRepoFollows */
    protected $repoFollows;


    /**
     * Construct
     *
     * @param iRepoFollows  $repoFollows  @IoC /module/profile/services/repository/Follows
     */
    function __construct(iRepoFollows $repoFollows)
    {
        $this->repoFollows  = $repoFollows;
    }


    /**
     * Cleanup Follow Requests that Rejected or Canceled
     *
     * @return array
     */
    function __invoke()
    {
        set_time_limit(0);

        $r = $this->repoFollows->findAllHasStatus([EntityFollow::STAT_REJECTED], null, 50);
        /** @var EntityFollow $fe */
        $i = 0;
        foreach ($r as $fe) {
            $this->repoFollows->deleteById($fe->getUid());

            // Sleep for half of second
            usleep(500000);
            $i++;
        }

        echo sprintf('Deleted %s Item(s).', $i);
        die;
    }
}
