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
    'clubs_paginator' => array(
        'type' => 'Zend_Controller_Router_Route_Regex',
        'route' => 'api/clubs/page/(\d+).*',
        'defaults' => array(
            'module' => 'api',
            'controller' => 'clubs',
            'page' => '1',
        ),
        'map' => array(
            1 => 'page',
        ),
        'reverse' => 'api/clubs/page/%s',
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
    'leagues_paginator' => array(
        'type' => 'Zend_Controller_Router_Route_Regex',
        'route' => 'api/leagues/page/(\d+).*',
        'defaults' => array(
            'module' => 'api',
            'controller' => 'leagues',
            'page' => '1',
        ),
        'map' => array(
            1 => 'page',
        ),
        'reverse' => 'api/leagues/page/%s',
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
    'league_clubs_paginator' => array(
        'type' => 'Zend_Controller_Router_Route_Regex',
        'route' => 'api/league/(\d+)/clubs/page/(\d+).*',
        'defaults' => array(
            'module' => 'api',
            'controller' => 'clubs',
            'leagueId' => '',
            'page' => 1,
        ),
        'map' => array(
            1 => 'leagueId',
            2 => 'page',
        ),
        'reverse' => 'api/league/%s/clubs/page/%s',
    ),
);