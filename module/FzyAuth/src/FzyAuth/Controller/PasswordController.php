<?php

namespace FzyAuth\Controller;

use FzyAuth\Exception\Password\NotSent;
use FzyCommon\Controller\AbstractController;
use Laminas\View\Model\ViewModel;
use FzyCommon\Util\Params;
use Laminas\Form\Form;
use LmcUser\Controller\UserController;

/**
 * Class PasswordController
 * @package Application\Controller
 */
class PasswordController extends AbstractController
{
    public function indexAction()
    {
        /* @var Form $form */
        $form  = $this->getServiceLocator()->get('FzyAuth\Form\ForgotPassword');

        return new ViewModel(array(
            'forgotForm' => $form,
        ));
    }
    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function forgotAction()
    {
        $params = $this->getParamsFromRequest();

        /* @var Form $form */
        $form  = $this->getServiceLocator()->get('FzyAuth\Form\ForgotPassword');

        $form->setData($params->getAll());
        $view = new ViewModel(array(
            'forgotForm' => $form,
        ));
        $view->setTemplate('fzy-auth/password/index');

        if (!$form->isValid()) {
            return $view;
        }
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        /* @var $forgotService \FzyAuth\Service\Password\Forgot */
        $forgotService = $this->getServiceLocator()->get('FzyAuth\Password\Forgot');
        try {
            $forgotService->handle($forgotService->getUserByEmail($params->get('email')));
        } catch (NotSent $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());

            return $view;
        } catch (\Exception $e) {
            // ignore all other errors
        }
        $this->flashMessenger()->addSuccessMessage('Please check your email for the password reset link.');

        return $this->redirect()->toRoute(UserController::ROUTE_LOGIN);
    }

    /**
     * @param  Params                          $params
     * @param  Form                            $form
     * @param  \FzyAuth\Service\Password\Reset $reset
     * @return \Laminas\Http\Response|ViewModel
     */
    protected function preReset(Params $params, Form $form, \FzyAuth\Service\Password\Reset $reset)
    {
        if (!trim($params->get('token')) || $reset->getUserByToken($params->get('token'))->isNull()) {
            return $this->redirect()->toRoute(UserController::ROUTE_LOGIN);
        }
        $form->setData($params->get());
        $view = new ViewModel(array(
            'changePasswordForm' => $form,
        ));
        $view->setTemplate('fzy-auth/password/reset');

        return $view;
    }

    public function resetAction()
    {
        return $this->preReset($this->getParamsFromRequest(), $this->getServiceLocator()->get('FzyAuth\Form\ChangePassword'), $this->getServiceLocator()->get('FzyAuth\Password\Reset'));
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function changeAction()
    {
        $params = $this->getParamsFromRequest();
        /* @var Form $form */
        $form  = $this->getServiceLocator()->get('FzyAuth\Form\ChangePassword');
        /* @var $resetService \FzyAuth\Service\Password\Reset */
        $resetService = $this->getServiceLocator()->get('FzyAuth\Password\Reset');
        $view = $this->preReset($params, $form, $resetService);
        if (!$view instanceof ViewModel) {
            // redirect
            return $view;
        }
        // validate form
        if (!$form->isValid()) {
            return $view;
        }

        try {
            $resetService->handle($resetService->getUserByToken($params->get('token')), $params);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());

            return $view;
        }
        $this->flashMessenger()->addSuccessMessage('Your password has been reset.');

        return $this->redirect()->toRoute(UserController::ROUTE_LOGIN);
    }

    protected function getSearchServiceKey()
    {
        throw new \RuntimeException('Search not authorized');
    }

    protected function getUpdateServiceKey()
    {
        throw new \RuntimeException('Update not authorized');
    }
}
