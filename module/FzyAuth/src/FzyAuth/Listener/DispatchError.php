<?php
namespace FzyAuth\Listener;

use FzyAuth\Service\AclEnforcer\Base as BaseAclEnforcer;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class DispatchError extends Base
{
    /**
     * @param MvcEvent $e
     */
    public function latch(MvcEvent $e)
    {
        $debug = $this->getModuleConfig()->get('debug', false);
        /* @var $apiRequestDetector \FzyAuth\Service\ApiRequestDetector */
        $apiRequestDetector = $this->getServiceLocator()->get('FzyAuth\Service\ApiRequestDetector');
        $moduleConfig = $this->getModuleConfig();
        if ($moduleConfig->get('intercept_api_errors', true)) {
            $this->latchTo(MvcEvent::EVENT_DISPATCH_ERROR,
            function (MvcEvent $e) use ($debug, $apiRequestDetector) {
                if ($apiRequestDetector->isApiRequest($e)) {
                    /* @var $exception \Exception */
                    $exception = $e->getParam('exception', new \Exception('Unknown Error', 500));
                    $viewData  = array(
                        'exception' => array(
                            'message' => $exception->getMessage(),
                            'code'    => $exception->getCode(),
                        ),
                    );
                    if ($debug) {
                        $viewData['exception']['trace'] = $exception->getTrace();
                    }
                    $view = new JsonModel($viewData);
                    $e->setViewModel($view);
                }
            });
        }
        if ($moduleConfig->get('intercept_web_errors', true)) {
            $this->latchTo(MvcEvent::EVENT_DISPATCH_ERROR,
            function (MvcEvent $e) use ($debug, $apiRequestDetector, $moduleConfig) {
                if (! $apiRequestDetector->isApiRequest($e)) {
                    $error = $e->getError();
                    if (empty($error) || $error != BaseAclEnforcer::ACL_ACCESS_DENIED) {
                        return;
                    }
                    // set up basic 403 error page
                    $result    = $e->getResult();
                    $baseModel = new ViewModel();
                    $baseModel->setTemplate($moduleConfig->get('web_error_layout', 'layout/layout'));
                    $model = new ViewModel();
                    $model->setTemplate($moduleConfig->get('web_error_template', 'error/403'));
                    $baseModel->addChild($model);
                    $baseModel->setTerminal(true);
                    $e->setViewModel($baseModel);
                    $response = $e->getResponse();
                    $response->setStatusCode(Response::STATUS_CODE_403);
                    $e->setResult($baseModel);

                    return $response;
                }
            }, - 999);
        }

        return $this;
    }
}
