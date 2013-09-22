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
 * A custom output filter for phpThumb
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var phpThumbOf $phpThumbOf
 * @var string $input
 * @var string|array $options
 *
 * @package phpthumbof
 */

if (empty($input)) {  // Exit quietly if no file name given
	return;
}

$scriptProperties['debug'] = isset($debug) ? $debug : FALSE;


static $pt_settings = array();

if (empty($pt_settings)) {
	if (!$modx->loadClass('phpThumbOf', MODX_CORE_PATH . 'components/phpthumbof/model/', true, true)) {
		$modx->log(modX::LOG_LEVEL_ERROR, '[pThumb] Could not load phpThumbOf class.');
		return $input;
	}
}

$phpThumbOf = new phpThumbOf($modx, $pt_settings, $scriptProperties);

if (!$phpThumbOf->cacheWritable) {
	return $input;
}

$thumbnail = $phpThumbOf->createThumbnail($input, $options);

$debug && $thumbnail !== $input &&   $phpThumbOf->debugmsg('', TRUE);

return $thumbnail;