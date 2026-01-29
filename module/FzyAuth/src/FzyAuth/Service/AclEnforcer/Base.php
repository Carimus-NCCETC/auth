<?php
namespace FzyAuth\Service\AclEnforcer;

use FzyAuth\Service\Acl;
use FzyAuth\Service\AclEnforcerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;

abstract class Base extends \FzyAuth\Service\Base implements AclEnforcerInterface
{
    const ACL_ACCESS_DENIED = 'acl_access_denied';

    protected $acl;

    /**
     * Do any setup
     *
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function init(MvcEvent $e)
    {
    }

    /**
     * Add acl to the view model
     *
     * @param ModelInterface $viewModel
     *
     * @return mixed
     */
    public function attachToView(ModelInterface $viewModel)
    {
        $viewModel->setVariable('acl', $this->getAcl());

        return $this;
    }

    /**
     * @return \Laminas\Permissions\Acl\Acl
     */
    public function getAcl()
    {
        if (!isset($this->acl)) {
            $this->acl = $this->getServiceLocator()->get('FzyAuth\Acl');
        }

        return $this->acl;
    }

    /**
     * Abstraction to use current user role (guest if not logged in)
     *
     * @param $resource
     * @param null $privilege
     *
     * @return mixed
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $this->getAcl()->isAllowed($this->getCurrentUser()->getRole(), $resource, $privilege);
    }

    /**
     * @param MvcEvent $e
     * @param $routeName
     * @param $action
     *
     * @return mixed
     */
    public function isAllowedToRoute(MvcEvent $e, $routeName, $controller, $action)
    {
        if ($this->hasControllerResource($controller)) {
            return $this->isAllowed($controller, $action);
        }

        return $this->isAllowed($routeName, $action);
    }

    /**
     * @param $routeName
     *
     * @return mixed
     */
    public function hasRoute(MvcEvent $e, $routeName, $controller, $action)
    {
        return $this->hasRouteResource($routeName) || $this->hasControllerResource($controller);
    }

    /**
     * @param $routeName
     *
     * @return bool
     */
    public function hasRouteResource($routeName)
    {
        return $this->getAcl()->hasResource(static::RESOURCE_ROUTE_PREFIX.$routeName);
    }

    /**
     * @param $controller
     *
     * @return bool
     */
    public function hasControllerResource($controller)
    {
        return $this->getAcl()->hasResource(static::RESOURCE_CONTROLLER_PREFIX.$controller);
    }

    /**
     * @param MvcEvent $e
     * @param $routeName
     * @param array    $routeParams
     * @param array    $routeOptions
     *
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function redirectTo(MvcEvent $e, $routeName, $routeParams = array(), $routeOptions = array())
    {
        $response = $e->getResponse();
        $url = $this->url()->fromRoute($routeName, $routeParams, $routeOptions);
        $response->getHeaders()->addHeaderLine('Location', $url);

        return $this->triggerStatus($e, \Laminas\Http\Response::STATUS_CODE_302);
    }

    /**
     * @param MvcEvent $e
     * @param int      $status
     *
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function triggerStatus(MvcEvent $e, $status = \Laminas\Http\Response::STATUS_CODE_404)
    {
        $response = $e->getResponse();
        $response->setStatusCode($status);
        $response->sendHeaders();
        $e->stopPropagation(true);

        return $response;
    }
}
