<?php
/**
 * pThumb
 * Copyright 2013-2014 Jason Grant
 *
 * Please see the GitHub page for documentation or to report bugs:
 * https://github.com/oo12/phpThumbOf
 *
 * Forked from phpThumbOf 1.4.0
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
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
 */
/**
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string|array $options
 *
 */

if (empty($input)) {  // Exit quietly if no file name given
	return;
}

$scriptProperties['debug'] = isset($debug) ? $debug : false;


static $pt_settings = array();

if (empty($pt_settings)) {
	if (!$modx->loadClass('phpThumbOf', MODX_CORE_PATH . 'components/phpthumbof/model/', true, true)) {
		$modx->log(modX::LOG_LEVEL_ERROR, '[pThumb] Could not load phpThumbOf class.');
		return $input;
	}
}

$pThumb = new phpThumbOf($modx, $pt_settings, $scriptProperties);

$result = $pThumb->createThumbnail($input, $options);

if (!empty($toPlaceholder) || $result['outputDims']) {
	if ($result['width'] === '' && $result['file'] && $dims = getimagesize($result['file']) ) {
			$result['width'] = $dims[0];
			$result['height'] = $dims[1];
	}
	if (!empty($toPlaceholder)) {
		$modx->setPlaceholders(array(
			$toPlaceholder => $result['src'],
			"$toPlaceholder.width" => $result['width'],
			"$toPlaceholder.height" => $result['height']
		));
		$output = '';
	}
	if ($result['outputDims']) {
		$output = "src=\"{$result['src']}\"" . ($result['width'] ? " width=\"{$result['width']}\" height=\"{$result['height']}\"" : '');
	}
}
else {
	$output = $result['src'];
}

if ($debug && $result['success']) {  // if debugging is on and createThumbnail was successful, log the debug info
	$pThumb->debugmsg(isset($pThumb->phpThumb->debugmessages) ? ':: Processed ::' : ":: Loaded from cache: {$result['src']}", true);
}

return $output;