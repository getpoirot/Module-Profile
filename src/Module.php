<?php
namespace Module\Profile
{
    use Module\Content\Services\ServiceClientTender;
    use Poirot\Application\aSapi;
    use Poirot\Application\Interfaces\iApplication;
    use Poirot\Application\Interfaces\Sapi;
    use Poirot\Application\ModuleManager\Interfaces\iModuleManager;
    use Poirot\Application\Sapi\Module\ContainerForFeatureActions;
    use Poirot\Ioc\Container;
    use Poirot\Ioc\Container\BuildContainer;
    use Poirot\Router\BuildRouterStack;
    use Poirot\Router\Interfaces\iRouterStack;
    use Poirot\Std\Interfaces\Struct\iDataEntity;


    /**
     * - Using Mongo Db To Store Content.
     *
     *   @see mod-content.conf.php
     *
     *
     * - Using Tender-Bin Storage For Files.
     *   through http client-tenderBin
     *
     *   also using oauth-client.
     *
     *   @see ServiceClientTender
     *
     */
    class Module implements Sapi\iSapiModule
        , Sapi\Module\Feature\iFeatureModuleInitSapi
        , Sapi\Module\Feature\iFeatureModuleInitModuleManager
        , Sapi\Module\Feature\iFeatureModuleMergeConfig
        , Sapi\Module\Feature\iFeatureModuleNestActions
        , Sapi\Module\Feature\iFeatureModuleNestServices
        , Sapi\Module\Feature\iFeatureOnPostLoadModulesGrabServices
    {
        const CONF = 'module.content';


        /**
         * Init Module Against Application
         *
         * - determine sapi server, cli or http
         *
         * priority: 1000 A
         *
         * @param iApplication|aSapi $sapi Application Instance
         *
         * @return false|null False mean not setup with other module features (skip module)
         * @throws \Exception
         */
        function initialize($sapi)
        {
            if ( \Poirot\isCommandLine( $sapi->getSapiName() ) )
                // Sapi Is Not HTTP. SKIP Module Load!!
                return false;
        }

        /**
         * Initialize Module Manager
         *
         * priority: 1000 C
         *
         * @param iModuleManager $moduleManager
         *
         * @return void
         */
        function initModuleManager(iModuleManager $moduleManager)
        {
            // ( ! ) ORDER IS MANDATORY

            if (!$moduleManager->hasLoaded('MongoDriver'))
                // MongoDriver Module Is Required.
                $moduleManager->loadModule('MongoDriver');

            if (!$moduleManager->hasLoaded('OAuth2Client'))
                // Load OAuth2 Client To Assert Tokens.
                $moduleManager->loadModule('OAuth2Client');

        }

        /**
         * Register config key/value
         *
         * priority: 1000 D
         *
         * - you may return an array or Traversable
         *   that would be merge with config current data
         *
         * @param iDataEntity $config
         *
         * @return array|\Traversable
         */
        function initConfig(iDataEntity $config)
        {
            return \Poirot\Config\load(__DIR__ . '/../../config/mod-content');
        }

        /**
         * Get Action Services
         *
         * priority not that serious
         *
         * - return Array used to Build ModuleActionsContainer
         *
         * @return array|ContainerForFeatureActions|BuildContainer|\Traversable
         */
        function getActions()
        {
            return \Poirot\Config\load(__DIR__ . '/../../config/mod-content.actions');
        }

        /**
         * Get Nested Module Services
         *
         * it can be used to manipulate other registered services by modules
         * with passed Container instance as argument.
         *
         * priority not that serious
         *
         * @param Container $moduleContainer
         *
         * @return null|array|BuildContainer|\Traversable
         */
        function getServices(Container $moduleContainer = null)
        {
            $conf = \Poirot\Config\load(__DIR__ . '/../../config/mod-content.services');
            return $conf;
        }

        /**
         * Resolve to service with name
         *
         * - each argument represent requested service by registered name
         *   if service not available default argument value remains
         * - "services" as argument will retrieve services container itself.
         *
         * ! after all modules loaded
         *
         * @param iRouterStack $router
         */
        function resolveRegisteredServices(
            $router = null
        ) {
            # Register Http Routes:
            if ($router) {
                $routes = include __DIR__ . '/../../config/mod-content.routes.conf.php';
                $buildRoute = new BuildRouterStack;
                $buildRoute->setRoutes($routes);
                $buildRoute->build($router);
            }
        }
    }

}


namespace Module\Content\Actions
{
    use Module\Content\Actions\Comments\AddCommentOnPostAction;
    use Module\Content\Actions\Comments\ListCommentsOfPostAction;
    use Module\Content\Actions\Comments\RemoveCommentFromPostAction;
    use Module\Content\Actions\Likes\LikePostAction;
    use Module\Content\Actions\Likes\ListPostLikesAction;
    use Module\Content\Actions\Likes\ListPostsWhichUserLikedAction;
    use Module\Content\Actions\Likes\UnLikePostAction;
    use Module\Content\Actions\Posts\BrowsePostsAction;
    use Module\Content\Actions\Posts\CreatePostAction;
    use Module\Content\Actions\Posts\DeletePostAction;
    use Module\Content\Actions\Posts\EditPostAction;
    use Module\Content\Actions\Posts\ListPostsOfMeAction;
    use Module\Content\Actions\Posts\ListPostsOfUserAction;
    use Module\Content\Actions\Posts\RetrievePostAction;


    /**
     * @property CreatePostAction      $CreatePostAction
     * @property EditPostAction        $EditPostAction
     * @property DeletePostAction      $DeletePostAction
     * @property RetrievePostAction    $RetrievePostAction
     * @property ListPostsOfMeAction   $ListPostsOfMeAction
     * @property ListPostsOfUserAction $ListPostsOfUserAction
     * @property BrowsePostsAction     $BrowsePostsAction
     *
     * @property LikePostAction                $LikePostAction
     * @property UnLikePostAction              $UnLikePostAction
     * @property ListPostLikesAction           $ListPostLikesAction
     * @property ListPostsWhichUserLikedAction $ListPostsWhichUserLikedAction
     *
     * @property AddCommentOnPostAction      $AddCommentOnPostAction
     * @property RemoveCommentFromPostAction $RemoveCommentFromPostAction
     * @property ListCommentsOfPostAction    $ListCommentsOfPostAction
     *
     */
    class IOC extends \IOC
    { }
}

namespace Module\Content\Services
{
    use Poirot\TenderBinClient\Client;

    /**
     * @method static ContainerCappedContentObject ContentObjectContainer()
     * @method static Client ClientTender()
     */
    class IOC extends \IOC
    { }
}
