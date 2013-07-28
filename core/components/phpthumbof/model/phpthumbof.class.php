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
 * @package phpThumbOf
 */
class phpThumbOf {

public $phpThumb;
public $cacheWritable = TRUE;
public $success = TRUE;

private $modx;
private $input;

function __construct(modX &$modx, &$settings_cache, $debug, $options = array()) {
	$this->modx =& $modx;
	$this->config =& $settings_cache;
	if (empty($this->config)) {  // first time through, get and store all the settings
		$this->config['debug'] = $debug;
		$this->config['corePath'] = MODX_CORE_PATH . 'components/phpthumbof/';
		$this->config['assetsPath'] = $modx->getOption('phpthumbof.assets_path', $options, $modx->getOption('assets_path') . 'components/phpthumbof/');
		$this->config['assetsUrl'] = $modx->getOption('phpthumbof.assets_url', $options, $modx->getOption('assets_url') . 'components/phpthumbof/');
		$this->config['cachePath'] = $modx->getOption('phpthumbof.cache_path', $options, $this->config['assetsPath'] . 'cache/', TRUE);
		$this->config['cachePath'] = str_replace(
			array('[[+core_path]]', '[[+assets_path]]', '[[+base_path]]', '[[+manager_path]]'),
			array(MODX_CORE_PATH, MODX_ASSETS_PATH,	MODX_BASE_PATH,	MODX_MANAGER_PATH),
			$this->config['cachePath']
		);
		$this->config['cachePathUrl'] = $modx->getOption('phpthumbof.cache_url', $options, $this->config['assetsUrl'] . 'cache/', TRUE);
		$this->config['basePath'] = MODX_BASE_PATH;
		$this->config['fixDupSubdir'] = $modx->getOption('phpthumbof.fix_dup_subdir', $options, TRUE);
		$this->config['jpegQuality'] = $modx->getOption('phpthumbof.jpeg_quality', $options, 75);
		$this->config['checkModTime'] = $modx->getOption('phpthumbof.check_mod_time', $options, FALSE);
		$this->config['hashThumbnailNames'] = $modx->getOption('phpthumbof.hash_thumbnail_names', $options, FALSE);
		$this->config['postfixPropertyHash'] = $modx->getOption('phpthumbof.postfix_property_hash', $options, TRUE);
		$this->config['newFilePermissions'] = $modx->getOption('new_file_permissions', $options, 0664);

		if (!is_writable($this->config['cachePath'])) {  // check that the cache directory is writable
			if (!$modx->cacheManager->writeTree($this->config['cachePath'])) {
				$modx->log(modX::LOG_LEVEL_ERROR, '[pThumb] Cache path not writable: ' . $this->config['cachePath']);
				$this->cacheWritable = $this->success = FALSE;
			}
		}
	}
}

/*
 *  Write current resource id, image filename and $msg to the MODX error log.
 *  if $phpthumbDebug, also write the phpThumb debugmessages array
 */
public function debugmsg($msg, $phpthumbDebug = FALSE) {
	$this->modx->log(
		modX::LOG_LEVEL_ERROR,
		"[pThumb] Resource: {$this->modx->resource->get('id')} || Image: " .
		(isset($this->input) ? $this->input : '(none)') .
		($msg ? "\n$msg" : '') .
		($phpthumbDebug && isset($this->phpThumb->debugmessages) ? "\nphpThumb debug output:" . substr(print_r($this->phpThumb->debugmessages, TRUE), 7, -2) . "----------------------\n" : '')
	);
}

/*
 *  Create a thumnail from $src with $options
 *  $src can be a path/filename or URL and absolute or relative
 *  Returns the filename of the cached image on success or $src on failure
 */
public function createThumbnail($src, $options) {
	if ( preg_match('/^(?:https?:)?\/\/(.+?)\/(.+)/i', $src, $matches) ) {  // if we've got a remote image to work with
		$file = $this->config['cachePath'] . preg_replace("/[^\w\d\-_\.]/", '-', "{$matches[1]}-{$matches[2]}");
		if (!file_exists($file)) {  // if it's not in our cache, go get it
			$fh = fopen($file, 'wb');
			if (!$fh) {
				$this->success = FALSE;
				return $src;
			}
			$curlFail = FALSE;
			$ch = curl_init($src);
			curl_setopt_array($ch, array(
				CURLOPT_TIMEOUT	=> $this->config['remoteTimeout'],
				CURLOPT_FILE => $fh,
				CURLOPT_FAILONERROR => TRUE
			));
			curl_exec($ch);  // download the file and store it in $fh
			if (curl_errno($ch)) {
				$this->debugmsg("Retrieving $src\nTarget filename: $file\ncURL error: " . curl_error($ch) . "  *** Skipping ***\n");
				$curlFail = TRUE;
			}
			curl_close($ch);
			fclose($fh);
			if ($curlFail) {  // if we didn't get it, skip and don't cache
				unlink($file);
				$this->success = FALSE;
				return $src;
			}
		}
	}
	else {
		if (file_exists($src)) {  // if we've already got an existing file, keep going
			$file = $src;
		}
		else {  // otherwise prepend basePath and try again
			if ($this->config['fixDupSubdir']) {  // fixes path problems when MODX is installed in a subdir
				$topdir = substr($src, 0, strpos($src, '/', 1) + 1);
				$topdir_len = strlen($topdir);
				if ($topdir === substr($this->config['basePath'], -$topdir_len)) {
					$src = substr($src, $topdir_len);
				}
			}
			$file = $this->config['basePath'] . rawurldecode(ltrim($src, '/'));  // Fix spaces and other encoded characters in the filename
			if (!file_exists($file)) {
				$this->debugmsg("File not found: $file  *** Skipping ***");
				$this->success = FALSE;
				return $src;
			}
		}
	}
	$this->input = $file;


	/* Process options. Set $ptOptions */
	if (!is_array($options)) {  // convert options string to array
		parse_str($options, $ptOptions);
	}
	else {  // otherwise use the original phpThumbOf code
		$ptOptions = array();
		foreach ($options as $opt) {
			$opt = explode('=', $opt);
			$key = str_replace('[]','',$opt[0]);
			if (!empty($key)) {
				/* allow arrays of options */
				if (isset($ptOptions[$key])) {
					if (is_string($ptOptions[$key])) {
						$ptOptions[$key] = array($ptOptions[$key]);
					}
					$ptOptions[$key][] = $opt[1];
				} else { /* otherwise pass in as string */
					$ptOptions[$key] = $opt[1];
				}
			}
		}
	}
	if (empty($ptOptions['f'])) {  // if filetype isn't already set, set it based on extension
		$ext = strtolower( pathinfo($this->input, PATHINFO_EXTENSION) );
		$ptOptions['f'] = ($ext === 'png' || $ext === 'gif') ? $ext : 'jpeg';
	}
	if (($ptOptions['f'] === 'jpeg' || $ptOptions['f'] === 'jpg') && empty($ptOptions['q'])) {
		$ptOptions['q'] = $this->config['jpegQuality'];  // use global jpeg quality if needed
	}


	/* Determine cache filename. Set $cacheKey and $cacheUrl */
	$modtime = $this->config['checkModTime'] ? @filemtime($this->input) : '';
	if ($this->config['hashThumbnailNames']) {  // either hash the filename
		$cacheFilename = md5($this->input . $modtime) . '.' . md5(serialize($ptOptions)) . '.' . $ptOptions['f'];
	}
	else {  // or attempt to preserve the filename
		$cacheFilename = basename($this->input);
		if ($this->config['postfixPropertyHash']) {
			$cacheFilename = pathinfo($cacheFilename, PATHINFO_FILENAME);
			/* for PHP < 5.2 use:
			$cut = strrpos($cacheFilename, '.');
			if ($cut) { $cacheFilename = substr($cacheFilename, 0, $cut); } */
			$cacheFilename .= '.' . md5( serialize($ptOptions) . pathinfo($this->input, PATHINFO_DIRNAME) . $modtime) .
				'.' . ($ptOptions['f'] === 'jpeg' ? 'jpg' : $ptOptions['f']);
		}
	}
	$cacheKey = $this->config['cachePath'] . $cacheFilename;
	$cacheUrl = $this->config['cachePathUrl'] . rawurlencode($cacheFilename);

	if (file_exists($cacheKey)) {  // If the file's in the cache, we're done.
		return $cacheUrl;
	}


	/* Startup the phpThumb service. */
	if (!class_exists('modPhpThumb')) {
		if (!$this->modx->loadClass('modPhpThumb', MODX_CORE_PATH . 'model/phpthumb/', true, true)) {
			$this->debugmsg('Could not load modPhpThumb class.');
			$this->success = FALSE;
			return $src;
		}
	}
	$this->phpThumb = new modPhpThumb($this->modx);
	$this->phpThumb->config = array_merge($this->phpThumb->config, $ptOptions);
	$this->phpThumb->setParameter('config_document_root', $this->config['basePath']);
	$this->phpThumb->initialize();

	$this->phpThumb->setParameter('config_cache_directory', $this->config['cachePath']);
	$this->phpThumb->setParameter('config_allow_src_above_phpthumb', TRUE);
	$this->phpThumb->setParameter('allow_local_http_src', TRUE);
	$this->phpThumb->setCacheDirectory();
	$this->phpThumb->set($this->input);


	if (!$this->phpThumb->GenerateThumbnail()) {  // create the thumbnail
		$this->debugmsg('Could not generate thumbnail', TRUE);
		$this->success = FALSE;
		return $src;
	}
	if ($this->phpThumb->RenderToFile($cacheKey)) {  // write it to the cache file
		@chmod($cacheKey, $this->config['newFilePermissions']);  // make sure file permissions are correct
		return $cacheUrl;
	}
	else {
		$this->debugmsg("Could not cache thumbnail to file at: {$cacheKey}", TRUE);
		$this->success = FALSE;
		return $src;
	}
}


/*
 *  Clean up the phpThumbOf cache directory
 *  Used by phpThumbOfCacheManager
 *  Adapted from phpThumb ( http://phpthumb.sourceforge.net/ )
 */
public function cleanCache() {
	$cache_maxage = $this->modx->getOption('phpthumb_cache_maxage', NULL, 30) * 86400;
	$cache_maxsize = $this->modx->getOption('phpthumb_cache_maxsize', NULL, 100) * 1048576;
	$cache_maxfiles = (int) $this->modx->getOption('phpthumb_cache_maxfiles', NULL, 10000);
	$this->modx->log(modX::LOG_LEVEL_INFO, 'phpThumbOfCacheManager: Cleaning phpThumbOf cache...');
	$this->modx->log(modX::LOG_LEVEL_INFO, ":: Max Age: $cache_maxage seconds || Max Size: $cache_maxsize bytes || Max Files: $cache_maxfiles");

	if (!($cache_maxage || $cache_maxsize || $cache_maxfiles)) {
		return;
	}

	$DeletedKeys = array();
	$AllFilesInCacheDirectory = array();
	$dirname = rtrim(realpath($this->config['cachePath']), '/\\');
	if ($dirhandle = @opendir($dirname)) {
		while (($file = readdir($dirhandle)) !== FALSE) {
			$fullfilename = $dirname . DIRECTORY_SEPARATOR . $file;
			if (is_file($fullfilename) && preg_match('/(jpe?g|png|gif)$/', $file)) {
				$AllFilesInCacheDirectory[] = $fullfilename;
			}
		}
		closedir($dirhandle);
	}
	$totalimages = count($AllFilesInCacheDirectory);
	$this->modx->log(modX::LOG_LEVEL_INFO, ":: $totalimages images in the phpThumbOf cache");

	if (empty($AllFilesInCacheDirectory)) {
		return;
	}

	$CacheDirOldFilesAge  = array();
	$CacheDirOldFilesSize = array();
	foreach ($AllFilesInCacheDirectory as $fullfilename) {
		$CacheDirOldFilesAge[$fullfilename] = @fileatime($fullfilename);
		if ($CacheDirOldFilesAge[$fullfilename] == 0) {
			$CacheDirOldFilesAge[$fullfilename] = @filemtime($fullfilename);
		}
		$CacheDirOldFilesSize[$fullfilename] = @filesize($fullfilename);
	}
	$DeletedKeys['zerobyte'] = array();
	foreach ($CacheDirOldFilesSize as $fullfilename => $filesize) {
		// purge all zero-size files more than an hour old (to prevent trying to delete just-created and/or in-use files)
		$cutofftime = time() - 3600;
		if (($filesize == 0) && ($CacheDirOldFilesAge[$fullfilename] < $cutofftime)) {
			if (@unlink($fullfilename)) {
				$DeletedKeys['zerobyte'][] = $fullfilename;
				unset($CacheDirOldFilesSize[$fullfilename]);
				unset($CacheDirOldFilesAge[$fullfilename]);
			}
		}
	}
	$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . count($DeletedKeys['zerobyte']) . ' zero-byte images');
	asort($CacheDirOldFilesAge);

	if ($cache_maxfiles) {
		$TotalCachedFiles = count($CacheDirOldFilesAge);
		$DeletedKeys['maxfiles'] = array();
		foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
			if ($TotalCachedFiles > $cache_maxfiles) {
				if (@unlink($fullfilename)) {
					--$TotalCachedFiles;
					$DeletedKeys['maxfiles'][] = $fullfilename;
				}
			} else {  // there are few enough files to keep the rest
				break;
			}
		}
		$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . count($DeletedKeys['maxfiles']) . " images based on (cache_maxfiles=$cache_maxfiles)");
		foreach ($DeletedKeys['maxfiles'] as $fullfilename) {
			unset($CacheDirOldFilesAge[$fullfilename]);
			unset($CacheDirOldFilesSize[$fullfilename]);
		}
	}

	if ($cache_maxage) {
		$mindate = time() - $cache_maxage;
		$DeletedKeys['maxage'] = array();
		foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
			if ($filedate) {
				if ($filedate < $mindate) {
					if (@unlink($fullfilename)) {
						$DeletedKeys['maxage'][] = $fullfilename;
					}
				} else {  // the rest of the files are new enough to keep
					break;
				}
			}
		}
		$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . count($DeletedKeys['maxage']) . ' images based on (cache_maxage='. $cache_maxage / 86400 .' days)');
		foreach ($DeletedKeys['maxage'] as $fullfilename) {
			unset($CacheDirOldFilesAge[$fullfilename]);
			unset($CacheDirOldFilesSize[$fullfilename]);
		}
	}

	if ($cache_maxsize) {
		$TotalCachedFileSize = array_sum($CacheDirOldFilesSize);
		$DeletedKeys['maxsize'] = array();
		foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
			if ($TotalCachedFileSize > $cache_maxsize) {
				if (@unlink($fullfilename)) {
					$TotalCachedFileSize -= $CacheDirOldFilesSize[$fullfilename];
					$DeletedKeys['maxsize'][] = $fullfilename;
				}
			} else {  // the total filesizes are small enough to keep the rest of the files
				break;
			}
		}
		$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . count($DeletedKeys['maxsize']) . ' images based on (cache_maxsize=' . $cache_maxsize / 1048576 . ' MB)');
		foreach ($DeletedKeys['maxsize'] as $fullfilename) {
			unset($CacheDirOldFilesAge[$fullfilename]);
			unset($CacheDirOldFilesSize[$fullfilename]);
		}
	}

	$totalpurged = 0;
	foreach ($DeletedKeys as $key => $value) {
		$totalpurged += count($value);
	}
	$this->modx->log(modX::LOG_LEVEL_INFO, ":: Purged $totalpurged images out of $totalimages");
}

}