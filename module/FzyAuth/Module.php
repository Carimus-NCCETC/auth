<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace FzyAuth;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;

/**
 * Class Module
 * @package Application
 */
class Module implements BootstrapListenerInterface
{
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
	    return array(
		    'Laminas\Loader\ClassMapAutoloader' => array(
			    __DIR__ . '/autoload_classmap.php',
		    ),
		    'Laminas\Loader\StandardAutoloader' => array(
			    'namespaces' => array(
				    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
			    ),
		    ),
	    );
    }

	/**
	 * Listen to the bootstrap event
	 *
	 * @param EventInterface $e
	 *
	 * @return array
	 */
	public function onBootstrap( EventInterface $e ) {
		/* @var $e \Laminas\Mvc\MvcEvent */
		/* @var $sm \Laminas\ServiceManager\ServiceLocatorInterface */
		$sm = $e->getApplication()->getServiceManager();
		// enforce ACL on route requests
		$sm->get('FzyAuth\Listener\Route')->latch($e);
		$sm->get('FzyAuth\Listener\Register')->latch($e);
		$sm->get('FzyAuth\Listener\DispatchError')->latch($e);
	}
}
