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
  'area' => 'Resizer',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.jpeg_quality',
  'value' => 75,
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.check_mod_time',
  'value' => TRUE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.hash_thumbnail_names',
  'value' => FALSE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.postfix_property_hash',
  'value' => TRUE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.cache_url',
  'value' => '',
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'paths',
), '', true, true);
$systemSettings[$ssIdx] = $modx->newObject('modSystemSetting');
$systemSettings[$ssIdx++]->fromArray(array (
  'key' => 'phpthumbof.cache_path',
  'value' => '',
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'paths',
), '', true, true);
return $systemSettings;
