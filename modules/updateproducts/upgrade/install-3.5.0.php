<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_3_5_0($object)
{
	return $object->installDb();
}


