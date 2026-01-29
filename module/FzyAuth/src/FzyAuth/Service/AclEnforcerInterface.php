<?php
namespace FzyAuth\Service;

use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;

interface AclEnforcerInterface
{
    const RESOURCE_ROUTE_PREFIX = 'rt/';
    const RESOURCE_CONTROLLER_PREFIX = 'ctrl/';

    /**
     * Do any setup
     *
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function init(MvcEvent $e);

    /**
     * Add acl to the view model
     * @param ModelInterface $viewModel
     *
     * @return mixed
     */
    public function attachToView(ModelInterface $viewModel);

    /**
     * @return Acl object
     */
    public function getAcl();

    /**
     * Abstraction to use current user role (guest if not logged in)
     * @param $resource
     * @param null $privilege
     *
     * @return mixed
     */
    public function isAllowed($resource, $privilege = null);

    /**
     * @param MvcEvent $e
     * @param $routeName
     * @param $action
     *
     * @return mixed
     */
    public function isAllowedToRoute(MvcEvent $e, $routeName, $controller, $action);

    /**
     * @param $routeName
     *
     * @return mixed
     */
    public function hasRoute(MvcEvent $e, $routeName, $controller, $action);

    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleRouteMissing(MvcEvent $e);

    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleAllowed(MvcEvent $e);

    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleNotAllowed(MvcEvent $e);

    /**
     * @param $routeName
     *
     * @return bool
     */
    public function hasRouteResource($routeName);

    /**
     * @param $controller
     *
     * @return bool
     */
    public function hasControllerResource($controller);
}
