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
if (empty($ptOptions['f'])){
    $ext = pathinfo($input, PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
            $ptOptions['f'] = $ext;
            break;
        default:
            $ptOptions['f'] = 'jpeg';
            break;
    }
}

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
$phpThumb->setParameter('config_document_root',$modx->getOption('base_path',$scriptProperties,MODX_BASE_PATH));
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
$cacheUrl = $assetsUrl.'cache/'.str_replace($cacheDir,'',$cacheKey);
$cacheUrl = str_replace('//','/',$cacheUrl);

/* ensure we have an accurate and clean cache directory */
$phpThumb->CleanUpCacheDirectory();

/* debugging code */
if ($debug) {
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $tstart = $mtime;
    set_time_limit(0);

    $oldLogTarget = $modx->getLogTarget();
    $oldLogLevel = $modx->getLogLevel();
    $modx->setLogLevel(modX::LOG_LEVEL_DEBUG);
    $logTarget = $modx->getOption('debugTarget',$scriptProperties,'');
    if (!empty($logTarget)) {
        $modx->setLogTarget();
    }
}
/* if using s3, check for file there */
$expired = false;
if ($useS3) {
    $s3bucket = $modx->getOption('phpthumbof.s3_bucket',$scriptProperties,'');
    $s3hostDefault = $s3bucket.'.s3.amazonaws.com/';

    /* if using a CNAME alias, set here (ensure is postfixed with /) */
    $s3hostAlias = $modx->getOption('phpthumbof.s3_host_alias',$scriptProperties,'');
    $s3hostAliasLen = strlen($s3hostAlias);
    if (!empty($s3hostAlias)) {
        $s3hostAlias = str_replace(array('http://','https://'),'',$s3hostAlias);
        if (substr($s3hostAlias,$s3hostAliasLen-1,$s3hostAliasLen) != '/') {
            $s3hostAlias .= '/';
        }
    }
    $s3host = !empty($s3hostAlias) ? $s3hostAlias : $s3hostDefault;

    /* calc relative path of image in s3 bucket */
    $path = str_replace('//','/',$s3path.$cacheFilename);
    $expired = true;

    /* check with php's get_headers (slower) */
    if ($modx->getOption('phpthumbof.s3_headers_check',$scriptProperties,false)) {
        $modx->log(modX::LOG_LEVEL_DEBUG,'[phpthumbof] Using get_headers to check modified.');
        $s3imageUrl = 'http://'.str_replace('//','/',$s3host.urlencode($path));
        $headers = get_headers($s3imageUrl,1);

        if (!empty($headers) && !empty($headers[0]) && $headers[0] == 'HTTP/1.1 200 OK') {
            if (empty($headers['Last-Modified'])) {
                $expired = true;
            } else {
                $lastModified = $headers['Last-Modified'];
                $lastModified = strtotime(trim($lastModified[1]));
            }
        } else {
            $expired = true;
        }
        
    } else { /* otherwise use amazon's (faster) get object info */
        $modx->log(modX::LOG_LEVEL_DEBUG,'[phpthumbof] Using get_object_url to check modified.');
        $s3response = $modaws->getFileUrl($path);
        if (!empty($s3response) && is_object($s3response) && !empty($s3response->body) && !empty($s3response->status) && $s3response->status == 200) {
            /* check expiry for image */
            $lastModified = strtotime($s3response->header['last-modified']);
            $s3imageUrl = $s3response->header['_info']['url'];

            if (!empty($s3hostAlias)) {
                $s3imageUrl = str_replace($s3hostDefault,$s3hostAlias,$s3imageUrl);
            }
        }
    }
    
    /* check to see if expired */
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
        if ($debug) {
            $modx->log(modX::LOG_LEVEL_DEBUG,"\n".'[phpthumb] Not expired, returning.');
            $mtime= microtime();
            $mtime= explode(" ", $mtime);
            $mtime= $mtime[1] + $mtime[0];
            $tend= $mtime;
            $totalTime= ($tend - $tstart);
            $totalTime= sprintf("%2.4f s", $totalTime);

            $modx->log(modX::LOG_LEVEL_DEBUG,"\n<br />Execution time: {$totalTime}\n<br />");
        }
        return $s3imageUrl;
    } else {
        $modaws->deleteObject($path);
    }
}

/* ensure file has proper permissions */
if (!empty($cacheKey)) {
    $filePerm = (int)$modx->getOption('new_file_permissions',$scriptProperties,'0664');
    @chmod($cacheKey, octdec($filePerm));
}

if ($debug) {
    $mtime= microtime();
    $mtime= explode(" ", $mtime);
    $mtime= $mtime[1] + $mtime[0];
    $tend= $mtime;
    $totalTime= ($tend - $tstart);
    $totalTime= sprintf("%2.4f s", $totalTime);

    $modx->log(modX::LOG_LEVEL_DEBUG,"\n<br />Execution time: {$totalTime}\n<br />");
    $modx->setLogLevel($oldLogLevel);
    $modx->setLogTarget($oldLogTarget);
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