<?php

return array(
	'clubs' => array(
		'type' => 'Zend_Controller_Router_Route_Regex',
		'route' => 'api/clubs/(\d+).*',
		'defaults' => array(
			'module' => 'api',
			'controller' => 'clubs',
			'id' => '',
		),
		'map' => array(
			1 => 'id',
		),
		'reverse' => 'api/clubs/%s',
	),
	'leagues' => array(
		'type' => 'Zend_Controller_Router_Route_Regex',
		'route' => 'api/leagues/(\d+).*',
		'defaults' => array(
			'module' => 'api',
			'controller' => 'leagues',
			'id' => '',
		),
		'map' => array(
			1 => 'id',
		),
		'reverse' => 'api/leagues/%s',
	),
    'league_clubs' => array(
        'type' => 'Zend_Controller_Router_Route_Regex',
        'route' => 'api/league/(\d+)/clubs.*',
        'defaults' => array(
            'module' => 'api',
            'controller' => 'clubs',
            'leagueId' => '',
        ),
        'map' => array(
            1 => 'leagueId',
        ),
        'reverse' => 'api/league/%s/clubs',
    ),
);