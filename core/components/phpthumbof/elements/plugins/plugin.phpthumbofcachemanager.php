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
 * Handles cache management for phpthumbof filter
 *
 * @var \modX $modx
 * @var array $scriptProperties
 *
 * @package phpthumbof
 */

/*	Currently this does nothing because of a sort of bug in the core
	phpThumb class.  Cache cleanup didn't do anything in phpThumbOf
	either.
*/

if (empty($results)) $results = array();

if ($modx->event->name === 'OnSiteRefresh') {
	if (!$modx->loadClass('modPhpThumb', $modx->getOption('core_path').'model/phpthumb/',true,true)) {
		$modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not load modPhpThumb class in plugin.');
		return;
	}
	$modelPath = $modx->getOption('phpthumbof.core_path', null, $modx->getOption('core_path').'components/phpthumbof/') . 'model/';
	require_once $modelPath . 'phpthumbof/phpthumbof.class.php';
	$phpThumbOf = new phpThumbOf($modx);
	$phpThumbOf->getCacheDirectory();
	$phpThumbOf->ensureCacheDirectoryIsWritable();
	$thumbnail = $phpThumbOf->createThumbnailObject();
	$thumbnail->initializeService();
	$thumbnail->cleanCache();
}

return;