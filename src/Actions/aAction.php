<?php
namespace Module\Profile\Actions;

use Poirot\Application\Exception\exAccessDenied;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2Client\Interfaces\iAccessToken;


/**
 *
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
{
    /** @var iHttpRequest */
    protected $request;

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


    /**
     * Assert Token
     *
     * @param iAccessToken $token
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
