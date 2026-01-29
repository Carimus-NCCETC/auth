<?php
namespace FzyAuth\Service\AclEnforcer;

use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use LmcUser\Controller\UserController;

class Web extends Base
{
    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleRouteMissing(MvcEvent $e)
    {
        $routeName = 'home';
        if ($e->getRouteMatch()->getMatchedRouteName() == $routeName) {
            // prevent infinite loop
            exit();
        }

        return $this->redirectTo($e, $routeName);
    }

    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleAllowed(MvcEvent $e)
    {
        // do nothing
    }

    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleNotAllowed(MvcEvent $e)
    {
        // is this user authenticated?
        if (!$this->getCurrentUser()->isNull()) {
            // not allowed to this route by the ACL
            $app = $e->getTarget();
            $route = $e->getRouteMatch();
            $e->setError(self::ACL_ACCESS_DENIED)
                ->setParam('route', $route->getMatchedRouteName());
            $app->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $e);

            return;
        }
        // redirect to login
        if ($e->getRouteMatch()->getMatchedRouteName() == UserController::ROUTE_LOGIN) {
            // prevent infinite loop
            return $this->triggerStatus($e, Response::STATUS_CODE_403);
        }

        return $this->redirectTo($e, UserController::ROUTE_LOGIN);
    }
}
