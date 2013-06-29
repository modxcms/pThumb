<?php
/**
 * pThumb
 *
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 * Further modifications Copyright 2013 Jason Grant
 *
 * Please see the GitHub page for documentation or to report bugs:
 * https://github.com/oo12/phpThumbOf
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
	return '';
}
if (!$modx->loadClass('modPhpThumb',$modx->getOption('core_path').'model/phpthumb/',true,true)) {
	$modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not load modPhpThumb class.');
	return '';
}

$modelPath = $modx->getOption('phpthumbof.core_path',null,$modx->getOption('core_path').'components/phpthumbof/').'model/';
require_once $modelPath.'phpthumbof/phpthumbof.class.php';
$phpThumbOf = new phpThumbOf($modx,$scriptProperties);

$phpThumbOf->getCacheDirectory();
$phpThumbOf->ensureCacheDirectoryIsWritable();

$thumbnail = $phpThumbOf->createThumbnailObject();
$thumbnail->setInput($input);
$thumbnail->setOptions($options);
$thumbnail->initializeService();
return $thumbnail->render();