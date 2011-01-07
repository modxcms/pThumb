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
/* if using s3, load service class */
$useS3 = $modx->getOption('phpthumbof.use_s3',$scriptProperties,false);
if ($useS3) {
    $modelPath = $modx->getOption('phpthumbof.core_path',null,$modx->getOption('core_path').'components/phpthumbof/').'model/';
    $modaws = $modx->getService('modaws','modAws',$modelPath.'aws/',$scriptProperties);
    $s3path = $modx->getOption('phpthumbof.s3_path',null,'phpthumbof/');
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
$cacheDir = $assetsPath.'cache/';

/* check to make sure cache dir is writable */
if (!is_writable($cacheDir)) {
    if (!$modx->cacheManager->writeTree($cacheDir)) {
        $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Cache dir not writable: '.$assetsPath.'cache/');
        return '';
    }
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
$cacheFilename = $inputSanitized;
$cacheFilename .= '.'.md5($options);
$cacheFilename .= '.' . (!empty($ptOptions['f']) ? $ptOptions['f'] : 'png');
$cacheKey = $assetsPath.'cache/'.$cacheFilename;

/* get cache Url */
$assetsUrl = $modx->getOption('phpthumbof.assets_url',$scriptProperties,$modx->getOption('assets_url').'components/phpthumbof/');
$cacheUrl = $assetsUrl.'cache/'.str_replace($phpThumb->config_cache_directory,'',$cacheKey);
$cacheUrl = str_replace('//','/',$cacheUrl);

/* ensure we have an accurate and clean cache directory */
$phpThumb->CleanUpCacheDirectory();

/* if using s3, check for file there */
$expired = false;
if ($useS3) {
    $path = str_replace('//','/',$s3path.$cacheFilename);
    $s3Url = $modaws->getFileUrl($path);
    if (!empty($s3Url) && is_object($s3Url) && !empty($s3Url->body) && !empty($s3Url->status) && $s3Url->status == 200) {
        /* check expiry for image */
        $lastModified = strtotime($s3response->header['last-modified']);
        if (!empty($lastModified)) {
            /* use last-modified to determine age */
            $maxAge = (int)$modx->getOption('phpthumbof.s3_cache_time',null,24) * 60 * 60;
            $now = time();
            if (($now - $lastModified) > $maxAge) {
                $expired = true;
            }
        }
        /* if not expired past the cache time, use that url. otherwise, delete from S3 */
        if (!$expired) {
            return $s3Url->header['_info']['url'];
        } else {
            $modaws->deleteObject($path);
        }
    }
}

/* check to see if there's a cached file of this already */
if (file_exists($cacheKey) && !$useS3 && !$expired) {
    $modx->log(modX::LOG_LEVEL_DEBUG,'[phpThumbOf] Using cached file found for thumb: '.$cacheKey);
    return $cacheUrl;
}

/* actually make the thumbnail */
if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
    if ($phpThumb->RenderToFile($cacheKey)) {
        /* if using s3, upload there and remove locally */
        if ($modx->getOption('phpthumbof.use_s3',$scriptProperties,false)) {
            $response = $modaws->upload($cacheKey,$s3path);
            if (!empty($response)) {
                $cacheUrl = $response;
                @unlink($cacheKey);
            }
        }
        return $cacheUrl;
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not cache thumb "'.$input.'" to file at: '.$cacheKey.' - Debug: '.print_r($phpThumb->debugmessages,true));
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not generate thumbnail: '.$input.' - Debug: '.print_r($phpThumb->debugmessages,true));
}
return '';