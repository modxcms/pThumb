<?php
/**
 * Subpackage transport file for pThumb extra
 *
 * Copyright 2013 by Jason Grant
 * Created on 08-25-2013
 *
 * @package phpthumbof
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
	function stripPhpTags($filename) {
		$o = file_get_contents($filename);
		$o = str_replace('<' . '?' . 'php', '', $o);
		$o = str_replace('?>', '', $o);
		$o = trim($o);
		return $o;
	}
}
/* @var $modx modX */
/* @var $sources array */
/** Package in subpackages
 *
 * @var modX $modx
 * @var modPackageBuilder $builder
 * @var array $sources
 * @package articles
 */
$subpackages = array (
  'resizer' => 'resizer-0.2.0-beta3',
);
$spAttr = array('vehicle_class' => 'xPDOTransportVehicle');

foreach ($subpackages as $name => $signature) {
	$vehicle = $builder->createVehicle(array(
		'source' => $sources['subpackages'] . $signature.'.transport.zip',
		'target' => "return MODX_CORE_PATH . 'packages/';",
	), $spAttr);
	$vehicle->validate('php',array(
		'source' => $sources['validators'].'validate.'.$name.'.php'
	));
	$vehicle->resolve('php',array(
		'source' => $sources['resolvers'].'packages/resolve.'.$name.'.php'
	));
	$builder->putVehicle($vehicle);
}
return true;
