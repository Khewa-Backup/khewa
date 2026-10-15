<?php
class Dispatcher extends DispatcherCore
{
		'category_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'categories' =>		array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
			),
		),
		'supplier_rule' => array(
			'controller' =>	'supplier',
			'rule' =>		'supplier/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'supplier_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'manufacturer_rule' => array(
			'controller' =>	'manufacturer',
			'rule' =>		'manufacturer/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'manufacturer_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_category_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/category/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'module' => array(
			'controller' =>	null,
			'rule' =>		'module/{module}{/:controller}',
			'keywords' => array(
				'module' =>			array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
				'controller' =>		array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
			),
			'params' => array(
				'fc' => 'module',
			),
		),
		'product_rule' => array(
			'controller' =>	'product',
			'rule' =>		'{categories:/}{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'product_rewrite'),
				'id_product_attribute' => array('regexp' => '[0-9]+'),
				'ean13' =>			array('regexp' => '[0-9\pL]*'),
				'category' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'categories' =>		array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
				'reference' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'manufacturer' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'supplier' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'price' =>			array('regexp' => '[0-9\.,]*'),
				'tags' =>			array('regexp' => '[a-zA-Z0-9-\pL]*'),
			),
		),
		
		'layered_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}/filter{selected_filters}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				
				'selected_filters' =>		array('regexp' => '.*', 'param' => 'selected_filters'),
				'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
	);
	
	
	
	
	
	
	
	
    
}