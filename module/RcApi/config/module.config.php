<?php
return array(
    'rc_api' => array(
        'page_size' => 10, // number of status items to return by default
    ),
    'phlyrestfully' => array(
        'renderer' => array(
            'hydrators' => array(
                'RcApi\Clubs' => 'Hydrator\ClassMethods',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'rc_clubs_api' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/api/clubs',
                    'defaults' => array(
                        'controller' => 'RcApi\ClubsResourceController',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'public' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/public',
                        ),
                    ),
                    'club' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/club[/:id]',
                            'defaults' => array(
                                'controller' => 'RcApi\ClubsResourceController',
                            ),
                            'constraints' => array(
                                'id' => '[0-9]{1,11}',
                            ),
                        ),
                    ),
                    //'tracks' => array(
                        //'type' => 'Segment',
                        //'options' => array(
                            //'route'    => '/track/:track[/:id]',
                            //'defaults' => array(
                                //'controller' => 'RcApi\ClubsResourceTrackController',
                            //),
                            //'constraints' => array(
                                //'track' => '[a-z0-9_-]+',
                                //'id'    => '[a-f0-9]{5,40}',
                            //),
                        //),
                    //),
                    'documentation' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/documentation',
                            'defaults' => array(
                                'controller' => 'PhlySimplePage\Controller\Page',
                                'template'   => 'rc_clubs_api/documentation',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'collection' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/collection',
                                    'defaults' => array(
                                        'template'   => 'rc_clubs_api/documentation/collection',
                                    ),
                                ),
                            ),
                            'clubs' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/clubs',
                                    'defaults' => array(
                                        'template'   => 'rc_clubs_api/documentation/clubs',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        )
    ),
    'service_manager' => array(
        'aliases' => array(
            'RcApi\DbAdapter' => 'Zend\Db\Adapter\Adapter',
            'RcApi\PersistenceListener' => 'RcApi\ClubDbPersistence',
        ),
        'invokables' => array(
            'Hydrator\ClassMethods' => 'Zend\Stdlib\Hydrator\ClassMethods',
        ),
        'factories' => array(
            'RcApi\DbTable' => 'RcApi\Service\DbTableFactory',
            'RcApi\ClubDbPersistence' => 'RcApi\Service\ClubDbPersistenceFactory',
            'RcApi\ClubResource' => 'RcApi\Service\ClubResourceFactory',
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'RcApi\ClubsResourceController' => 'RcApi\Service\ClubsResourceControllerFactory',
            //'RcApi\ClubsResourceTrackController' => 'RcApi\Service\ClubsResourceTrackControllerFactory',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'rc_clubs_api/documentation' => __DIR__ . '/../view/rc_api/documentation.phtml',
            'rc_clubs_api/documentation/collection' => __DIR__ . '/../view/rc_api/documentation/collection.phtml',
            'rc_clubs_api/documentation/clubs' => __DIR__ . '/../view/rc_api/documentation/clubs.phtml',
        ),
    ),
);
