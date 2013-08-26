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
			// remove some old settings on upgrade
			$oldSettings = array('phpthumbof.graphics_library', 'phpthumbof.fix_dup_subdir');
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