<?php
/**
 * pThumb
 * Copyright 2013 Jason Grant
 *
 * Forked from phpThumbOf 1.4.0
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 *
 * Please see the GitHub page for documentation or to report bugs:
 * https://github.com/oo12/phpThumbOf
 *
 * pThumb is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * pThumb is distributed in the hope that it will be useful, but WITHOUT ANY
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

$settings['phpthumbof.use_resizer'] = $modx->newObject('modSystemSetting');
$settings['phpthumbof.use_resizer']->fromArray(array(
	'key' => 'phpthumbof.use_resizer',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'phpthumbof',
	'area' => 'Resizer',
), '', true, true);

$settings['phpthumbof.graphics_library'] = $modx->newObject('modSystemSetting');
$settings['phpthumbof.graphics_library']->fromArray(array(
	'key' => 'phpthumbof.graphics_library',
	'value' => '2',
	'xtype' => 'textfield',
	'namespace' => 'phpthumbof',
	'area' => 'Resizer',
), '', true, true);

return $settings;