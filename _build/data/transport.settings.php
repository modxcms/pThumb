<?php
/**
 * systemSettings transport file for pThumb extra
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
/* @var xPDOObject[] $systemSettings */


$systemSettings = array();

$ssIdx = 1;

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.use_resizer',
	'value' => FALSE,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Images',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.global_defaults',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Images',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.remote_timeout',
	'value' => 5,
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Other',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.postfix_property_hash',
	'value' => TRUE,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [phpThumbOf]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.cache_path',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [phpThumbOf]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.cache_url',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [common]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.clean_level',
	'value' => '0',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [common]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.use_ptcache',
	'value' => FALSE,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [pThumb]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.ptcache_location',
	'value' => 'assets/image-cache',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [pThumb]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.ptcache_images_basedir',
	'value' => 'assets',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [pThumb]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'phpthumbof.check_mod_time',
	'value' => FALSE,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Cache [pThumb]',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.s3_output',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Amazon S3',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.s3_headers',
	'xtype' => 'textarea',
	'namespace' => 'phpthumbof',
	'area' => 'Amazon S3',
), '', true, true);

$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
	'key' => 'pthumb.s3_multi_img',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Amazon S3',
), '', true, true);

return $systemSettings;