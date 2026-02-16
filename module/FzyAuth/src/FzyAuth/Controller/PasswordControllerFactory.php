<?php

namespace FzyAuth\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PasswordControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $forgotPasswordForm = $container->get('FzyAuth\Form\ForgotPassword');
        $changePasswordForm = $container->get('FzyAuth\Form\ChangePassword');
        $forgotService = $container->get('FzyAuth\Password\Forgot');
        $resetService = $container->get('FzyAuth\Password\Reset');

        return new PasswordController(
            $forgotPasswordForm,
            $changePasswordForm,
            $forgotService,
            $resetService
        );
    }
}