<?php
namespace FzyAuth\Factory;

use FzyAuth\Util\Acl\Resource as AclResource;
use FzyCommon\Util\Params;
use Laminas\Permissions\Acl\Acl as LaminasAcl;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Acl
{
    /**
     * @param Params $roleConfig
     *
     * @return LaminasAcl
     */
    public function createAcl(Params $roleConfig, ServiceLocatorInterface $sm)
    {
        $acl = new LaminasAcl();
        // add all roles from config
        foreach ($roleConfig->get('roles', array()) as $roleName => $roleData) {
            $roleMap = Params::create($roleData);
            $role = new \Laminas\Permissions\Acl\Role\GenericRole($roleName);
            $acl->addRole($role, $roleMap->get('inherits', array()));
            // add resources from config
            foreach ($roleMap->get('allow', array()) as $resourceData) {
                $this->addAllowedResource($acl, $role, AclResource::create(Params::create($resourceData)));
            }
            // add denies
            foreach ($roleMap->get('deny', array()) as $resourceData) {
                $this->addDeniedResource($acl, $role, AclResource::create(Params::create($resourceData)));
            }
        }

        // trigger event for post-resource setup
        return $acl;
    }

    /**
     * @param Acl $acl
     * @param $role
     * @param $resource
     * @param $privileges
     */
    protected function addAllowedResource(LaminasAcl $acl, $role, AclResource $resource)
    {
        $this->addAclResource($acl, $resource);
        $acl->allow($role, $resource->getResource(), $resource->getPrivileges());

        return $this;
    }

    /**
     * @param Acl $acl
     * @param $role
     * @param $resource
     * @param $privileges
     */
    protected function addDeniedResource(LaminasAcl $acl, $role, AclResource $resource)
    {
        $this->addAclResource($acl, $resource);
        $acl->deny($role, $resource->getResource(), $resource->getPrivileges());

        return $this;
    }

    /**
     * @param Acl $acl
     * @param $resource
     */
    protected function addAclResource(LaminasAcl $acl, AclResource $resource)
    {
        if (!$acl->hasResource($resource->getResource())) {
            $acl->addResource(new \Laminas\Permissions\Acl\Resource\GenericResource($resource->getResource()));
        }

        return $this;
    }
}
