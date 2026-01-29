<?php
namespace FzyAuth\Listener;

use FzyAuth\Service\AclEnforcerInterface;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\MvcEvent;

class Route extends Base
{
    public function latch(MvcEvent $e)
    {
        if ($this->getModuleConfig()->get('enforce_acl', true)) {
            $this->latchTo(MvcEvent::EVENT_ROUTE, array($this, 'checkAcl'));
        }
    }

    public function checkAcl(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof \Laminas\Http\Request) {
            return;
        }
        $route = $e->getRouteMatch()->getMatchedRouteName();

        /* @var $aclEnforcer \FzyAuth\Service\AclEnforcerInterface */
        $aclEnforcer = $this->getServiceLocator()->get('FzyAuth\AclEnforcerFactory');

        $aclEnforcer->init($e);
        $aclEnforcer->attachToView($e->getViewModel());
        $controller = $e->getRouteMatch()->getParam('controller');
        $action = $e->getRouteMatch()->getParam('action');

        $missing = true;
        if ($aclEnforcer->hasControllerResource($controller)) {
            $missing = false;
            if ($aclEnforcer->isAllowed(AclEnforcerInterface::RESOURCE_CONTROLLER_PREFIX.$controller, $action)) {
                return $aclEnforcer->handleAllowed($e);
            }
        }
        if ($aclEnforcer->hasRouteResource($route)) {
            $missing = false;
            if ($aclEnforcer->isAllowed(AclEnforcerInterface::RESOURCE_ROUTE_PREFIX.$route, $action)) {
                return $aclEnforcer->handleAllowed($e);
            }
        }

        return $missing ? $aclEnforcer->handleRouteMissing($e) : $aclEnforcer->handleNotAllowed($e);
    }
}
