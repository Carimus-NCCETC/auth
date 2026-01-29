<?php
namespace FzyAuth\View\Helper;

use FzyAuth\Service\AclEnforcerInterface;
use FzyCommon\View\Helper\Base;

/**
 * Invoked with $this->fzyAllowed()
 *
 * If an argument is provided to the view helper, it returns a boolean evaluation of whether the ACL permits the current user to access the resource. Second parameter is optional and regarded as the privilege.
 * If no arguments are provided, it returns $this which then allows you to use the other helper functions
 *  + toResource - effectively what providing arguments to the invocation would return
 *  + toController - prefixes resource as a controller before search in ACL
 *  + toRoute - prefixes resource as route before search in ACL
 *
 * Class Allowed
 * @package FzyAuth\View\Helper
 */
class Allowed extends Base
{
    /**
     * @param  null       $resource
     * @param  null       $privilege
     * @return $this|bool
     */
    public function __invoke($resource = null, $privilege = null)
    {
        if ($resource === null) {
            return $this;
        }

        return $this->toResource($resource, $privilege);
    }

    /**
     * @param $resource
     * @param  null $privilege
     * @return bool
     */
    public function toResource($resource, $privilege = null)
    {
        /* @var $acl \Laminas\Permissions\Acl\Acl */
        $acl = $this->getService('FzyAuth\Acl');
        /* @var $user \FzyAuth\Entity\Base\UserInterface */
        $user = $this->getService('FzyAuth\CurrentUser');

        return $acl->isAllowed($user->getRole(), $resource, $privilege);
    }

    /**
     * @param $controller
     * @param  null $action
     * @return bool
     */
    public function toController($controller, $action = null)
    {
        return $this->toResource(AclEnforcerInterface::RESOURCE_CONTROLLER_PREFIX.$controller, $action);
    }

    /**
     * @param $routeName
     * @param  null $privilege
     * @return bool
     */
    public function toRoute($routeName, $privilege = null)
    {
        return $this->toResource(AclEnforcerInterface::RESOURCE_ROUTE_PREFIX.$routeName, $privilege);
    }
}
