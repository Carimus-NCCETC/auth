<?php
namespace FzyAuth\Listener;

use Laminas\Mvc\MvcEvent;

interface ListenerInterface
{
    public function latch(MvcEvent $e);
}
