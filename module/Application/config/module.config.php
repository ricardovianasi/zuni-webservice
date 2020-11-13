<?php
return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
					),
				),
			),

			'default' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/[:controller[/:action][/:id]]',
					'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Index'
					)
				)
			)
		),
	),
	'service_manager' => array(
		'aliases' => array(
			'Application\Service\Auth' => 'auth',
		),
		'factories' => array(
			'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
			'auth' => function($sm) {
				$options = new \DoctrineModule\Options\Authentication();
				$options->setObjectManager($sm->get('Doctrine\ORM\EntityManager'));
				$options->setCredentialProperty("password");
				$options->setIdentityProperty('email');
				$options->setIdentityClass('Application\Entity\User');
				$options->setCredentialCallable(function(\Application\Entity\User $user, $pass) {
					return \Application\Security\Crypt::testPassword($pass, $user->getPassword());
				});
				$adapter = new \Application\Authentication\Adapter\Zuni($options);

				$auth = new \Application\Service\Auth();
				$auth->setAdapter($adapter);
				return $auth;
			},
			'Application\Event\Auth' => function($sm) {
				$authEvent = new \Application\Event\Auth();
				return $authEvent;
			},
			'logger' => function($sm) {
				$log = new \Application\Log\Logger();
				$log->setServiceManager($sm);
				return $log;
			}
		),
	),
	'controllers' => array(
		'invokables' => array(
			'Application\Controller\Index' => 'Application\Controller\IndexController',
			'Application\Controller\Profile' => 'Application\Controller\ProfileController',
			'Application\Controller\Album' => 'Application\Controller\AlbumController',
			'Application\Controller\User' => 'Application\Controller\UserController',
			'Application\Controller\Group' => 'Application\Controller\GroupController',
			'Application\Controller\Hash' => 'Application\Controller\HashController',
			'Application\Controller\Tag' => 'Application\Controller\TagController',
			'Application\Controller\Auth' => 'Application\Controller\AuthController',
			'Application\Controller\Upload' => 'Application\Controller\UploadController',
			'Application\Controller\Image' => 'Application\Controller\ImageController',
			'Application\Controller\Share' => 'Application\Controller\ShareController',
			'Application\Controller\Utils' => 'Application\Controller\UtilsController',
		    'Application\Controller\Author' => 'Application\Controller\AuthorController',
		    'Application\Controller\Location' => 'Application\Controller\LocationController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions'       => true,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map' => array(
				'application/layout'      => __DIR__ . '/../view/layout/layout.phtml',
				'application/error'       => __DIR__ . '/../view/layout/error.phtml',
				'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
				'error/404'               => __DIR__ . '/../view/error/404.phtml',
				'error/index'             => __DIR__ . '/../view/error/index.phtml',
		),
		'template_path_stack' => array(
				__DIR__ . '/../view',
		),
		'strategies' => array(
			'ViewJsonStrategy'
		),
	),
	'doctrine' => array(

		'configuration' => array(
			'odm_default' => array(
				'default_db' => 'zuni',
			)
		),

		'driver' => array(
			// defines an annotation driver with two paths, and names it `my_annotation_driver`
			'application_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(
					'/../src/Application/Entity',
				),
			),
			'application_mongo_driver' => array(
				'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
				'paths' => array(
					'/../src/Application/Document',
				),
			),
			// default metadata driver, aggregates all other drivers into a single one.
			// Override `orm_default` only if you know what you're doing
			'orm_default' => array(
				'drivers' => array(
					// register `my_annotation_driver` for any entity under namespace `My\Namespace`
					'Application\Entity' => 'application_driver'
				)
			),
			'odm_default' => array(
				'drivers' => array(
					'Application\Document' => 'application_mongo_driver'
				),
			),
		),

	)
);
