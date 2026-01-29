<?php
namespace FzyAuth;
use FzyAuth\Entity\Base\UserInterface;
use FzyAuth\Service\Password\Forgot;
use FzyAuth\Util\Acl\Resource;
use LmcUser\Controller\UserController;

return array(
    'router' => array(
        'routes' => array(
            'fzyauth-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/password',
                    'defaults' => array(
                        'controller' => 'FzyAuth\Controller\Password',
                    ),
                ),
                'child_routes' => array(
                    'reset' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reset[/:token]',
                        ),
                        'child_routes' => array(
                            'get' => array(
                                'type' => 'method',
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'action' => 'reset',
                                    ),
                                ),
                                'may_terminate' => true,
                            ),
                            'post' => array(
                                'type' => 'method',
                                'options' => array(
                                    'verb' => 'post',
                                    'defaults' => array(
                                        'action' => 'change',
                                    ),
                                ),
                                'may_terminate' => true,
                            ),
                        ),
                    ),
                    'forgot' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/forgot',
                        ),
                        'child_routes' => array(
                            'get' => array(
                                'type' => 'method',
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'action' => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                            ),
                            'post' => array(
                                'type' => 'method',
                                'options' => array(
                                    'verb' => 'post',
                                    'defaults' => array(
                                        'action' => 'forgot',
                                    ),
                                ),
                                'may_terminate' => true,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'FzyAuth\Controller\Password' => 'FzyAuth\Controller\PasswordController',
        ),
    ),
	'service_manager' => array(
		'invokables' => array(
			// FzyAuth\Factory\Acl doesn't extend Base, keeps as invokable
            'FzyAuth\Factory\Acl' => 'FzyAuth\Factory\Acl',
        ),
        'aliases' => array(
            'FzyAuth\Password\Forgot' => 'FzyAuth\Service\Password\Forgot',
            'FzyAuth\Password\Reset'  => 'FzyAuth\Service\Password\Reset',
        ),
		'factories' => array(
            'FzyAuth\Config' => function($sm) {
                /* @var $config \FzyCommon\Util\Params */
                $config = $sm->get('FzyCommon\Config');
                return $config->getWrapped(\FzyAuth\Service\Base::MODULE_CONFIG_KEY);
            },
			'FzyAuth\AclEnforcerFactory' => function($sm) {
                /* @var $detector \FzyAuth\Service\ApiRequestDetector */
                $detector = $sm->get('FzyAuth\Service\ApiRequestDetector');
				/* @var $application \Laminas\Mvc\Application */
				return $sm->get($detector->isApiRequest($sm->get('Application')->getMvcEvent()) ? 'FzyAuth\Service\AclEnforcer\Api' : 'FzyAuth\Service\AclEnforcer\Web');
			},
			/**
			 * Factory method for instantiating the configuration specified factory, running it
			 * on the ACL configuration and returning an ACL object.
			 * @return \Laminas\Permissions\Acl\Acl
			 */
			'FzyAuth\Acl' => function($sm) {
				/* @var $moduleConfig \FzyCommon\Util\Params */
				$moduleConfig = $sm->get('FzyAuth\Config');
				$aclConfig = $moduleConfig->getWrapped('acl');
				/* @var $aclFactory \FzyAuth\Factory\Acl */
				$aclFactory = $sm->get($moduleConfig->get('acl_factory', 'FzyAuth\Factory\Acl'));
				return $aclFactory->createAcl($aclConfig, $sm);
			},

            'FzyAuth\NullUser' => function($sm){
                $nullUserClass = $sm->get('FzyAuth\Config')->get('null_user_class', '\FzyAuth\Entity\Base\UserNull');
                return new $nullUserClass();
            },

			/**
			 * Factory to return a UserInterface object indicating the currently logged in user.
			 *
			 * A UserNull object is returned in the event the user is not logged in.
			 *
			 * @return \FzyAuth\Entity\Base\UserInterface
			 */
			'FzyAuth\CurrentUser' => function($sm) {
				$lmcAuth = $sm->get('lmcuser_auth_service');
				if ($lmcAuth->hasIdentity()) {
					return $lmcAuth->getIdentity();
				}
                return $sm->get('FzyAuth\NullUser');
			},
            /**
             * Returns a class that implements ZF2's Transport interface
             * @return \Laminas\Mail\Transport\TransportInterface
             */
            'FzyAuth\Mail\Transport' => function($sm) {
                return $sm->get('SlmMail\Mail\Transport\SesTransport');
            },

            'FzyAuth\Form\ForgotPassword' => function($sm){
                $options = $sm->get('lmcuser_module_options');
                $form = new \FzyAuth\Form\ForgotPassword(null, $options);
                return $form->setInputFilter(new \FzyAuth\Form\ForgotPasswordFilter($options));
            },
            'FzyAuth\Form\ChangePassword' => function($sm){
                $options = $sm->get('lmcuser_module_options');
                $form = new \FzyAuth\Form\ChangePassword(null, $options);
                return $form->setInputFilter(new \FzyAuth\Form\ChangePasswordFilter($options));
            },
		),
	),
    'view_helpers' => array(
        'aliases' => array(
            'fzyAllowed' => 'FzyAuth\View\Helper\Allowed',
        ),
    ),
	\FzyAuth\Service\Base::MODULE_CONFIG_KEY => array(
		// whether to display exception traces
		'debug' => true,
		// whether to intercept api errors
		'intercept_api_errors' => true,
		// whether to enforce the ACL on route events
		'enforce_acl' => true,
        // the route name for api requests
        'api_route_name' => 'api',
        // service to generate and configure ACL
        //'acl_factory' => 'FzyAuth\Factory\Acl',
		// layout to use on web dispatch errors
//		'web_error_layout' => 'layout/layout',
		// template to use on web dispatch errors
//		'web_error_template' => 'error/403'
		// whether to intercept API request errors
//		'intercept_api_errors' => true,
		// whether to intercept Web request errors
//		'intercept_web_errors' => true,
        // class name of a null user (non-authenticated)
//        'null_user_class' => '\FzyAuth\Entity\Base\UserNull',
        // name of service key to use to retrieve Mail transport class
//        'mail_transport_factory' => 'SlmMail\Mail\Transport\SesTransport',
        // email property of the doctrine entity
//        'user_email_property' => 'email',
        // computational cost of generating the password
//        'password_cost' => 14,
        Forgot::OPTIONS => array(
//            'invalid_user_error_message' => '',
//            'mail_not_sent_error_message' => '',
//            'from_email' => '',
//            'from_name' => '',
//            'copy_to' => '',
//            'reset_subject' => '',
            'view' => 'fzy-auth/emails/forgot',
            'view_vars' => array(),
        ),

        // ACL
        'acl' => array(
            'roles' => array(
                UserInterface::ROLE_GUEST => array(
                    'allow' => array(
                        array(
                            \FzyAuth\Util\Acl\Resource::KEY_CONTROLLER => 'FzyAuth\Controller\Password',
                            \FzyAuth\Util\Acl\Resource::KEY_ACTIONS => array('index', 'forgot', 'reset', 'change'),
                        ),
                        array(
                            Resource::KEY_ROUTE => UserController::ROUTE_LOGIN,
                        ),
                    ),
                ),
                UserInterface::ROLE_USER => array(
                    'deny' => array(
                        array(
                            \FzyAuth\Util\Acl\Resource::KEY_CONTROLLER => 'FzyAuth\Controller\Password',
                            \FzyAuth\Util\Acl\Resource::KEY_ACTIONS => array('index', 'forgot', 'reset', 'change'),
                        ),
                    ),
                ),
            ),
        ),
	),
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		),
		'configuration' => array(
			'orm_default' => array(
				'generate_proxies' => false,
			),
		),
	),
	/**
	 * Override lmcuser settings with our custom entity user class
	 */
	'lmcuser' => array(
		'user_entity_class'       => 'FzyAuth\Entity\Base\User',
		'enable_default_entities' => false,
		'enable_username' => true,
	),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
