<?php
/**
 * plugins transport file for pThumb extra
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
/* @var xPDOObject[] $plugins */


$plugins = array();

$plugins[1] = $modx->newObject('modPlugin');
$plugins[1]->fromArray(array (
	'id' => 1,
	'property_preprocess' => false,
	'name' => 'phpThumbOfCacheManager',
	'description' => 'Handles cache cleaning when clearing the Site Cache.',
	'properties' => NULL,
	'disabled' => false,
), '', true, true);
$plugins[1]->setContent(file_get_contents($sources['source_core'] . '/elements/plugins/phpthumbofcachemanager.plugin.php'));

return $plugins;