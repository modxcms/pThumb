<?php
/**
 * phpThumbOf
 *
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 *
 * phpThumbOf is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * phpThumbOf is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * phpThumbOf; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package phpthumbof
 */
/**
 * System Settings
 *
 * @var modX $modx
 * @package phpthumbof
 * @subpackage build
 */
$settings = array();

$settings['phpthumbof.cache_path']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.cache_path']->fromArray(array(
	'key' => 'phpthumbof.cache_path',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'paths',
),'',true,true);

$settings['phpthumbof.cache_url']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.cache_url']->fromArray(array(
	'key' => 'phpthumbof.cache_url',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'paths',
),'',true,true);

$settings['phpthumbof.hash_thumbnail_names']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.hash_thumbnail_names']->fromArray(array(
	'key' => 'phpthumbof.hash_thumbnail_names',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'general',
),'',true,true);

$settings['phpthumbof.postfix_property_hash']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.postfix_property_hash']->fromArray(array(
	'key' => 'phpthumbof.postfix_property_hash',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'general',
),'',true,true);

$settings['phpthumbof.check_mod_time'] = $modx->newObject('modSystemSetting');
$settings['phpthumbof.check_mod_time']->fromArray(array(
	'key' => 'phpthumbof.check_mod_time',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'general',
), '', true, true);

$settings['phpthumbof.fix_dup_subdir'] = $modx->newObject('modSystemSetting');
$settings['phpthumbof.fix_dup_subdir']->fromArray(array(
	'key' => 'phpthumbof.fix_dup_subdir',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'general',
), '', true, true);

$settings['phpthumbof.jpeg_quality'] = $modx->newObject('modSystemSetting');
$settings['phpthumbof.jpeg_quality']->fromArray(array(
	'key' => 'phpthumbof.jpeg_quality',
	'value' => '75',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'general',
), '', true, true);

$settings['phpthumbof.use_s3']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.use_s3']->fromArray(array(
	'key' => 'phpthumbof.use_s3',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_key']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_key']->fromArray(array(
	'key' => 'phpthumbof.s3_key',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_secret_key']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_secret_key']->fromArray(array(
	'key' => 'phpthumbof.s3_secret_key',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_bucket']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_bucket']->fromArray(array(
	'key' => 'phpthumbof.s3_bucket',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_host_alias']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_host_alias']->fromArray(array(
	'key' => 'phpthumbof.s3_host_alias',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_path']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_path']->fromArray(array(
	'key' => 'phpthumbof.s3_path',
	'value' => 'phpthumbof/',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_cache_time']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_cache_time']->fromArray(array(
	'key' => 'phpthumbof.s3_cache_time',
	'value' => 24,
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);

$settings['phpthumbof.s3_headers_check']= $modx->newObject('modSystemSetting');
$settings['phpthumbof.s3_headers_check']->fromArray(array(
	'key' => 'phpthumbof.s3_headers_check',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 's3',
),'',true,true);
/*
$settings['']= $modx->newObject('modSystemSetting');
$settings['']->fromArray(array(
	'key' => '',
	'value' => false,
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => '',
),'',true,true);*/

return $settings;