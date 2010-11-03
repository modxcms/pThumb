<?php
/**
 * phpThumbOf
 *
 * Copyright 2009-2011 by Shaun McCormick <shaun@modx.com>
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
 * @package phpthumbof
 */
if (empty($modx)) return '';
if (!$modx->loadClass('modPhpThumb',$modx->getOption('core_path').'model/phpthumb/',true,true)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not load modPhpThumb class.');
    return '';
}
if (empty($input)) {
    $modx->log(modX::LOG_LEVEL_DEBUG,'[phpThumbOf] Empty image path passed, aborting.');
    return '';
}

/* explode tag options */
$ptOptions = array();
$eoptions = explode('&',$options);
foreach ($eoptions as $opt) {
    $opt = explode('=',$opt);
    if (!empty($opt[0])) {
        $ptOptions[$opt[0]] = $opt[1];
    }
}
if (empty($ptOptions['f'])) $ptOptions['f'] = 'png';

/* load phpthumb */
$assetsPath = $modx->getOption('phpthumbof.assets_path',$scriptProperties,$modx->getOption('assets_path').'components/phpthumbof/');
$phpThumb = new modPhpThumb($modx,$ptOptions);

if (!is_writable($assetsPath.'cache/')) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Cache dir not writable: '.$assetsPath.'cache/');
    return '';
}

/* do initial setup */
$phpThumb->initialize();
$phpThumb->setParameter('config_cache_directory',$assetsPath.'cache/');
$phpThumb->setParameter('config_allow_src_above_phpthumb',true);
$phpThumb->setParameter('allow_local_http_src',true);
$phpThumb->setCacheDirectory();

/* get absolute url of image */
if (strpos($input,'/') != 0 && strpos($input,'http') != 0) {
    $input = $modx->getOption('base_url').$input;
} else {
    $input = urldecode($input);
}

/* set source */
$phpThumb->set($input);

/* setup cache filename that is unique to this tag */
$inputSanitized = str_replace(array(':','/'),'_',$input);
$cacheKey = $assetsPath.'cache/'.$inputSanitized;
$cacheKey .= '.'.md5($options);
$cacheKey .= '.' . (!empty($ptOptions['f']) ? $ptOptions['f'] : 'png');

/* get cache Url */
$assetsUrl = $modx->getOption('phpthumbof.assets_url',$scriptProperties,$modx->getOption('assets_url').'components/phpthumbof/');
$cacheUrl = $assetsUrl.'cache/'.str_replace($phpThumb->config_cache_directory,'',$cacheKey);
$cacheUrl = str_replace('//','/',$cacheUrl);

/* ensure we have an accurate and clean cache directory */
$phpThumb->CleanUpCacheDirectory();

/* check to see if there's a cached file of this already */
if (file_exists($cacheKey)) {
    $modx->log(modX::LOG_LEVEL_DEBUG,'[phpThumbOf] Using cached file found for thumb: '.$cacheKey);
    return $cacheUrl;
}

/* actually make the thumbnail */
if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
    if ($phpThumb->RenderToFile($cacheKey)) {
        return $cacheUrl;
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not cache thumb "'.$input.'" to file at: '.$cacheKey.' - Debug: '.print_r($phpThumb->debugmessages,true));
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not generate thumbnail: '.$input.' - Debug: '.print_r($phpThumb->debugmessages,true));
}
return '';