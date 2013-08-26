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

$systemSettings[1] = $modx->newObject('modSystemSetting');
$systemSettings[1]->fromArray(array (
  'key' => 'phpthumbof.use_resizer',
  'value' => FALSE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'Resizer',
), '', true, true);
$systemSettings[2] = $modx->newObject('modSystemSetting');
$systemSettings[2]->fromArray(array (
  'key' => 'phpthumbof.jpeg_quality',
  'value' => 75,
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[3] = $modx->newObject('modSystemSetting');
$systemSettings[3]->fromArray(array (
  'key' => 'phpthumbof.fix_dup_subdir',
  'value' => TRUE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[4] = $modx->newObject('modSystemSetting');
$systemSettings[4]->fromArray(array (
  'key' => 'phpthumbof.check_mod_time',
  'value' => TRUE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[5] = $modx->newObject('modSystemSetting');
$systemSettings[5]->fromArray(array (
  'key' => 'phpthumbof.hash_thumbnail_names',
  'value' => FALSE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[6] = $modx->newObject('modSystemSetting');
$systemSettings[6]->fromArray(array (
  'key' => 'phpthumbof.postfix_property_hash',
  'value' => TRUE,
  'xtype' => 'combo-boolean',
  'namespace' => 'phpthumbof',
  'area' => 'general',
), '', true, true);
$systemSettings[7] = $modx->newObject('modSystemSetting');
$systemSettings[7]->fromArray(array (
  'key' => 'phpthumbof.cache_url',
  'value' => '',
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'paths',
), '', true, true);
$systemSettings[8] = $modx->newObject('modSystemSetting');
$systemSettings[8]->fromArray(array (
  'key' => 'phpthumbof.cache_path',
  'value' => '',
  'xtype' => 'textfield',
  'namespace' => 'phpthumbof',
  'area' => 'paths',
), '', true, true);
return $systemSettings;
