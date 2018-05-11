<?php
namespace Module\Profile\Actions;

use Module\Profile\Events\EventsHeapOfProfile;
use Poirot\Application\Exception\exAccessDenied;
use Poirot\Events\Event\BuildEvent;
use Poirot\Events\Event\MeeterIoc;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessTokenEntity;


abstract class aAction
    extends \Module\Foundation\Actions\aAction
    implements iEventProvider
{
    const CONF = 'events';


    /** @var iHttpRequest */
    protected $request;
    /** @var EventsHeapOfProfile */
    protected $events;

    protected $tokenMustHaveOwner  = true;
    protected $tokenMustHaveScopes = array(

    );


    /**
     * aAction constructor.
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iHttpRequest $httpRequest)
    {
        $this->request = $httpRequest;
    }


    // Implement Events

    /**
     * Get Events
     *
     * @return EventsHeapOfProfile
     */
    function event()
    {
        if (! $this->events ) {
            // Build Events From Merged Config
            $conf   = $this->sapi()->config()->get( \Module\Profile\Module::CONF );
            $conf   = $conf[self::CONF];

            $events = new EventsHeapOfProfile;
            $builds = new BuildEvent([ 'meeter' => new MeeterIoc, 'events' => $conf ]);
            $builds->build($events);

            $this->events = $events;
        }

        return $this->events;
    }


    // ..

    /**
     * Assert Token
     *
     * @param iAccessTokenEntity $token
     *
     * @throws exAccessDenied
     */
    protected function assertTokenByOwnerAndScope($token)
    {
        # Validate Access Token
        \Module\OAuth2Client\Assertion\validateAccessToken(
            $token
            , (object) ['mustHaveOwner' => $this->tokenMustHaveOwner, 'scopes' => $this->tokenMustHaveScopes ]
        );

    }
}
