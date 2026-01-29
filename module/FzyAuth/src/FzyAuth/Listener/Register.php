<?php
namespace FzyAuth\Listener;

use FzyAuth\Entity\Base\UserInterface;
use Laminas\Mvc\MvcEvent;

class Register extends Base
{
    public function latch(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $em           = $eventManager->getSharedManager();

        $lmcServiceEvents = $e->getApplication()->getServiceManager()->get('lmcuser_user_service')->getEventManager();
        $lmcServiceEvents->attach('register', function ($e) {
            $form = $e->getParam('form');
            /* @var $user \FzyAuth\Entity\Base\UserInterface */
            $user = $e->getParam('user');
            $user->setRole(UserInterface::ROLE_USER);
        });
    }
}
