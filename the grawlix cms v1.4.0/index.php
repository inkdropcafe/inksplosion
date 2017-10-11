<?php

/* ! Setup * * * * * * * */

require('./_system/init.inc.php');

$menu_list = get_menu_items();

if ( is_null($menu_list) ) {
	die ('<h1>Is the chicken soup fresh?</h1><p>Your site has no menu items.</p>');
}

$milieu_list = get_milieu_list();
if ( is_null($milieu_list) ) {
	die ('<h1>The Batphone has no dial tone.</h1><p>Important site settings are missing.</p>');
}


/* ! Route page request * * * * * * * */

$route = new GrlxRoute(array('menu'=>$menu_list,'milieu'=>$milieu_list));
$key = $route->setRoute();
if ( $milieu_list['timezone'] && $milieu_list['timezone'] != '')
{
	date_default_timezone_set($milieu_list['timezone']);
}

if ( !$key ) {
	die ('<h1>The Batmobile lost its wheel!</h1><p>Grawlix can\'t determine the correct page route.</p>');
}

$args['path'] = $route->path;
$args['query'] = $route->query;
$args['menu'] = $menu_list;
$args['milieu'] = $milieu_list;

switch ( $key ) {
	case 'rss':
		$grlxPage = new GrlxPage_RSS($args);
		break;
	case 'json':
		$grlxPage = new GrlxPage_JSON($args);
		break;
	case 'comic-home':
		$grlxPage = new GrlxPage_Comic($args);
		$grlxPage->getComicHome();
		break;
	case 'comic-inside':
		$grlxPage = new GrlxPage_Comic($args);
		$grlxPage->getComicInside();
		break;
	case 'comic-archive':
		$grlxPage = new GrlxPage_Archive($args);
		break;
	case 'static':
		$grlxPage = new GrlxPage_Static($args);
		break;
	case '404':
		$args['load404'] = 1;
		$grlxPage = new GrlxPage_Static($args);
		break;
}


/* ! Build the page * * * * * * * */

$grlxPage->buildPage();
