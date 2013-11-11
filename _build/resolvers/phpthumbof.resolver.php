<?php
/**
 * Resolver for pThumb extra
 *
 * Copyright 2013 by Jason Grant
 * Created on 08-25-2013
 *
 * pThumb is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * pThumb is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * pThumb; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * @package phpthumbof
 * @subpackage build
 */

/* @var $object xPDOObject */
/* @var $modx modX */

/* @var array $options */

if ($object->xpdo) {
	$modx =& $object->xpdo;
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			// move an existing non-default jpeg quality setting into the new global options
			$jpegQuality = $modx->getOption('phpthumbof.jpeg_quality', null, false);
			if ($jpegQuality && $jpegQuality != '75') {
				$setting = $modx->getObject('modSystemSetting', 'pthumb.global_defaults');
				$setting->set('value', "q=$jpegQuality");
				$setting->save();
			}

			// Update the area on existing settings from previous versions
			$convertarea = array(
				'phpthumbof.check_mod_time' => 'Cache [common]',
				'pthumb.ptcache_images_basedir' => 'Cache [pThumb]',
				'pthumb.ptcache_location' => 'Cache [pThumb]',
				'pthumb.use_ptcache' => 'Cache [pThumb]',
				'pthumb.use_ptcache' => 'Cache [pThumb]',
				'phpthumbof.cache_path' => 'Cache [phpThumbOf]',
				'phpthumbof.postfix_property_hash' => 'Cache [phpThumbOf]',
				'phpthumbof.cache_url' => 'Cache [common]'
			);
			foreach ($convertarea as $setting => $area) {
				$setting = $modx->getObject('modSystemSetting', $setting);
				if ($setting && $setting->get('area') !== $area) {
					$setting->set('area', $area);
					$setting->save();
				}
			}

			// remove some old settings on upgrade
			$oldSettings = array('phpthumbof.graphics_library', 'phpthumbof.fix_dup_subdir', 'phpthumbof.hash_thumbnail_names', 'phpthumbof.jpeg_quality');
			foreach ($oldSettings as $key) {
				$setting = $modx->getObject('modSystemSetting', array('key' => $key));
				if ($setting) {
					$setting->remove();
				}
			}
			$success = TRUE;
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}

return true;