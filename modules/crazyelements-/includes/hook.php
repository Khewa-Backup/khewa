<?php

use CrazyElements\PrestaHelper;

$GetAlldisplayHooks = array(
	array(
		'id'   => 'displayTop',
		'name' => 'displayTop',
	),
	array(
		'id'   => 'displayTopColumn',
		'name' => 'displayTopColumn',
	),
	array(
		'id'   => 'displayHome',
		'name' => 'displayHome',
	),
	array(
		'id'   => 'displayBanner',
		'name' => 'displayBanner',
	),
	array(
		'id'   => 'displayNavFullWidth',
		'name' => 'displayNavFullWidth',
	),
	array(
		'id'   => 'displayAfterBodyOpeningTag',
		'name' => 'displayAfterBodyOpeningTag',
	),
	array(
		'id'   => 'displayShoppingCart',
		'name' => 'displayShoppingCart',
	),
	array(
		'id'   => 'displayFooterProduct',
		'name' => 'displayFooterProduct',
	),
	array(
		'id'   => 'displayFooterCategory',
		'name' => 'displayFooterCategory',
	),
	array(
		'id'   => 'displayFooterTop',
		'name' => 'displayFooterTop',
	),
	array(
		'id'   => 'displayFooterBefore',
		'name' => 'displayFooterBefore',
	),
	array(
		'id'   => 'displayFooter',
		'name' => 'displayFooter',
	),
	array(
		'id'   => 'displayFooterAfter',
		'name' => 'displayFooterAfter',
	),
	array(
		'id'   => 'displayLeftColumn',
		'name' => 'displayLeftColumn',
	)
);

$custom_hooks = PrestaHelper::get_option( 'crazy_custom_hooks' );
$custom_hooks = Tools::jsonDecode( $custom_hooks, true );
if(isset($custom_hooks)){
	$temparr = array();
	foreach($custom_hooks as $custom_hook => $mod_route){
		$temparr[] = array(
			'id' => $custom_hook,
			'name' => $custom_hook
		); 
	}
	$GetAlldisplayHooks = array_merge($GetAlldisplayHooks,$temparr);
}