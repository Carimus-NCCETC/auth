<?php
namespace FzyAuth\Service\AclEnforcer;

use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

class Api extends Base
{
    /**
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function handleRouteMissing(MvcEvent $e)
    {
        return $this->setResponseContent($this->triggerStatus($e, Response::STATUS_CODE_404), array(
            'message' => 'Route is missing from ACL',
        ));
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
        return $this->setResponseContent($this->triggerStatus($e, Response::STATUS_CODE_403), array(
            'message' => 'Route not permitted',
        ));
    }

    protected function setResponseContent(Response $response, array $data)
    {
        if ($response instanceof \Laminas\Http\PhpEnvironment\Response) {
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(json_encode(array_merge(array(
                'status' => $response->getStatusCode(),
            ), $data)));
        }

        return $response;
    }
}
