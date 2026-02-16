<?php

namespace FzyAuth\Controller;

use FzyAuth\Exception\Password\NotSent;
use FzyCommon\Controller\AbstractController;
use Laminas\View\Model\ViewModel;
use FzyCommon\Util\Params;
use Laminas\Form\Form;
use LmcUser\Controller\UserController;
use FzyAuth\Service\Password\Forgot as ForgotService;
use FzyAuth\Service\Password\Reset as ResetService;

/**
 * Class PasswordController
 * @package Application\Controller
 */
class PasswordController extends AbstractController
{
    /**
     * @var Form
     */
    protected $forgotPasswordForm;

    /**
     * @var Form
     */
    protected $changePasswordForm;

    /**
     * @var ForgotService
     */
    protected $forgotService;

    /**
     * @var ResetService
     */
    protected $resetService;

    public function __construct(
        Form $forgotPasswordForm,
        Form $changePasswordForm,
        ForgotService $forgotService,
        ResetService $resetService
    ) {
        $this->forgotPasswordForm = $forgotPasswordForm;
        $this->changePasswordForm = $changePasswordForm;
        $this->forgotService = $forgotService;
        $this->resetService = $resetService;
    }

    public function indexAction()
    {
        return new ViewModel(array(
            'forgotForm' => $this->forgotPasswordForm,
        ));
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function forgotAction()
    {
        $params = $this->getParamsFromRequest();

        $form = $this->forgotPasswordForm;

        $form->setData($params->getAll());
        $view = new ViewModel(array(
            'forgotForm' => $form,
        ));
        $view->setTemplate('fzy-auth/password/index');

        if (!$form->isValid()) {
            return $view;
        }

        try {
            $this->forgotService->handle($this->forgotService->getUserByEmail($params->get('email')));
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
     * @param  Params    $params
     * @param  Form      $form
     * @param  ResetService $reset
     * @return \Laminas\Http\Response|ViewModel
     */
    protected function preReset(Params $params, Form $form, ResetService $reset)
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
        return $this->preReset(
            $this->getParamsFromRequest(),
            $this->changePasswordForm,
            $this->resetService
        );
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function changeAction()
    {
        $params = $this->getParamsFromRequest();
        $form = $this->changePasswordForm;
        $view = $this->preReset($params, $form, $this->resetService);

        if (!$view instanceof ViewModel) {
            // redirect
            return $view;
        }
        // validate form
        if (!$form->isValid()) {
            return $view;
        }

        try {
            $this->resetService->handle($this->resetService->getUserByToken($params->get('token')), $params);
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