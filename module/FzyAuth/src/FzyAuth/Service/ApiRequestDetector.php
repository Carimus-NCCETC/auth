<?php

namespace FzyAuth\Service;

use Laminas\Mvc\MvcEvent;

class ApiRequestDetector extends Base
{
    protected $apiRouteName;

    public function getApiRouteName()
    {
        if (!isset($this->apiRouteName)) {
            $this->apiRouteName = $this->getModuleConfig()->get('api_route_name', 'api');
        }

        return $this->apiRouteName;
    }

    public function isApiRequest(MvcEvent $e)
    {
        $apiRouteName = $this->getApiRouteName();

        return $e->getRouteMatch() && ($route = $e->getRouteMatch()->getMatchedRouteName()) && ($route == $apiRouteName || strpos($route, $apiRouteName.'/') === 0);
    }
}
