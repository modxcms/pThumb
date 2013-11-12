<?php
/**
 * pThumb
 * Copyright 2013 Jason Grant
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
 */

require_once MODX_CORE_PATH . 'components/phpthumbof/model/phpthumbof.class.php';

/*
 *  Used for recursing through the pThumb Cache
 */
class FilenameFilter extends RecursiveRegexIterator {
	protected $regex;

	function __construct(RecursiveIterator $it, $regex) {
		$this->regex = $regex;
		parent::__construct($it, $regex);
	}

	function accept() {
		return (!$this->isFile() || preg_match($this->regex, $this->getFilename()));
	}
}


/*
 *  Extends main pThumb class to add a cache cleaning method
 *  Used by phpThumbOfCacheManager
 */
class pThumbCacheCleaner extends phpThumbOf {


private function pluralize($count, $thing = 'image') {
	return $count == 1 ? "1 $thing" : "$count $thing" . 's';
}


/*
 *  Clean up the pThumb cache directories
 *  Adapted from phpThumb ( http://phpthumb.sourceforge.net/ )
 *  Clean Levels:
 *  0: no cleaning
 *  1: clean based on system phpThumb cache settings
 *  2: remove all cached images
 */
public function cleanCache() {
	$config['clean_level'] = $this->modx->getOption('pthumb.clean_level', null, 0);
	$config['cache_maxage'] = $this->modx->getOption('phpthumb_cache_maxage', null, 365);
	$description['maxage'] = $this->pluralize($config['cache_maxage'], 'day');
	$config['cache_maxage'] *= 86400;  // convert to seconds
	$config['cache_maxsize'] = $this->modx->getOption('phpthumb_cache_maxsize', null, 300);
	$description['maxsize'] = "{$config['cache_maxsize']} MB";
	$config['cache_maxsize'] *= 1048576;  // convert to bytes
	$config['cache_maxfiles'] = (int) $this->modx->getOption('phpthumb_cache_maxfiles', null, 10000);
	$this->modx->log(modX::LOG_LEVEL_INFO, "[pThumb Cache Manager]  Clean Level: {$config['clean_level']} || Max Age: {$description['maxage']} || Max Size: {$description['maxsize']} || Max Files: {$config['cache_maxfiles']}");

	if (!$config['clean_level'] || $config['clean_level'] == 1 && !($config['cache_maxage'] || $config['cache_maxsize'] || $config['cache_maxfiles'])) {
		$this->modx->log(modX::LOG_LEVEL_INFO, '::  Skipping cache cleanup based on settings');
		$this->modx->log(modX::LOG_LEVEL_INFO, '');
		return;  // that was easy.
	}
	$cachepath = array();  // gather up cache paths
	$cachepath['pThumb'] = MODX_BASE_PATH . $this->modx->getOption('pthumb.ptcache_location', null, 'assets/image-cache', TRUE);
	$cachepath['phpThumbOf'] = $this->modx->getOption('phpthumbof.cache_path', null, "{$this->config['assetsPath']}components/phpthumbof/cache", true);
	$cachepath['Remote Images'] = $this->config['remoteImagesCachePath'];
	foreach ($cachepath as $path) {
		$path = rtrim(str_replace('//', '/', $path), '/');  // normalize path
		if (!is_dir($path)) {
			$path = false;
		}
	}

	$cachefiles = array();  // gather up cache files
	if ($cachepath['pThumb']) {  // recurse through all subdirectories looking for jpeg, jpg, png and gif
		$dir = new RecursiveDirectoryIterator($cachepath['pThumb'], FilesystemIterator::SKIP_DOTS);
		$filter = new FilenameFilter($dir, '/\.(?:jpe?g|png|gif)$/i');
		$cachefiles['pThumb'] = array();
		foreach(new RecursiveIteratorIterator($filter) as $file) {
			$cachefiles['pThumb'][] = $file->getPathName();
		}
	}
	foreach (array('phpThumbOf', 'Remote Images') as $dir) {
		if ($cachepath[$dir]) {
			$cachefiles[$dir] = glob("{$cachepath[$dir]}/*.{jp*g, png, gif}", GLOB_BRACE) == array();  // empty array if glob didn't find anything
		}
	}

	foreach ($cachefiles as $cachename => $fileset) {
		$totalimages = count($fileset);
		$DeletedKeys = array();
		$CacheDirOldFilesAge  = array();
		$CacheDirOldFilesSize = array();
		foreach ($fileset as $fullfilename) {  // get accessed (or modified) time and size for each file
			$CacheDirOldFilesAge[$fullfilename] = @fileatime($fullfilename) == @filemtime($fullfilename);
			$CacheDirOldFilesSize[$fullfilename] = @filesize($fullfilename);
		}


		$this->modx->log(modX::LOG_LEVEL_INFO, ":: $cachename Cache: " . $this->pluralize($totalimages) . ' (' . round(array_sum($CacheDirOldFilesSize) / 1048576, 2) . ' MB)');

		if ($config['clean_level'] == 2) {
			$deleted = 0;
			foreach ($fileset as $file) {
				if (@unlink($file)) {
					++$deleted;
				}
			}
			$this->modx->log(modX::LOG_LEVEL_INFO, ':: ' . $this->pluralize($deleted, 'file') . ' purged');
			continue;
		}

		$madedeletions = false;
		$DeletedKeys['zerobyte'] = 0;
		foreach ($CacheDirOldFilesSize as $fullfilename => $filesize) {  // remove any 0-byte files
			// but only if they're more than 10 min old (to prevent trying to delete just-created or in-use files)
			$cutofftime = time() - 600;
			if (!$filesize && $CacheDirOldFilesAge[$fullfilename] < $cutofftime) {
				if (@unlink($fullfilename)) {
					++$DeletedKeys['zerobyte'];
					unset($CacheDirOldFilesSize[$fullfilename]);
					unset($CacheDirOldFilesAge[$fullfilename]);
				}
			}
		}
		if ($DeletedKeys['zerobyte']) {
			$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . $this->pluralize($DeletedKeys['zerobyte'], 'zero-byte image'));
			$madedeletions = true;
		}

		asort($CacheDirOldFilesAge);  // all deletions start with the least recently accesed (or oldest) files first

		if ($config['cache_maxage']) {  // delete any files older that maxage
			$mindate = time() - $config['cache_maxage'];
			$DeletedKeys['maxage'] = 0;
			foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
				if ($filedate) {
					if ($filedate < $mindate) {
						if (@unlink($fullfilename)) {
							++$DeletedKeys['maxage'];
							unset($CacheDirOldFilesAge[$fullfilename]);
							unset($CacheDirOldFilesSize[$fullfilename]);
						}
					}
					else {  // the rest of the files are new enough to keep
						break;
					}
				}
			}
			if ($DeletedKeys['maxage']) {
				$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . $this->pluralize($DeletedKeys['maxage']) . " based on (cache_maxage={$description['maxage']})");
				$madedeletions = true;
			}
		}

		if ($config['cache_maxfiles']) {  // delete any files in excess of maxfiles
			$TotalCachedFiles = count($CacheDirOldFilesAge);
			$DeletedKeys['maxfiles'] = 0;
			foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
				if ($TotalCachedFiles > $config['cache_maxfiles']) {
					if (@unlink($fullfilename)) {
						--$TotalCachedFiles;
						++$DeletedKeys['maxfiles'];
						unset($CacheDirOldFilesAge[$fullfilename]);
						unset($CacheDirOldFilesSize[$fullfilename]);
					}
				}
				else {  // there are few enough files to keep the rest
					break;
				}
			}
			if ($DeletedKeys['maxfiles']) {
				$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . $this->pluralize($DeletedKeys['maxfiles']) . " based on (cache_maxfiles={$config['cache_maxfiles']})");
				$madedeletions = true;
			}
		}

		if ($config['cache_maxsize']) {  // delete files to get the total cache size under the maxsize limit
			$TotalCachedFileSize = array_sum($CacheDirOldFilesSize);
			$DeletedKeys['maxsize'] = 0;
			foreach ($CacheDirOldFilesAge as $fullfilename => $filedate) {
				if ($TotalCachedFileSize > $config['cache_maxsize']) {
					if (@unlink($fullfilename)) {
						$TotalCachedFileSize -= $CacheDirOldFilesSize[$fullfilename];
						++$DeletedKeys['maxsize'];
						unset($CacheDirOldFilesAge[$fullfilename]);
						unset($CacheDirOldFilesSize[$fullfilename]);
					}
				}
				else {  // the total filesizes are small enough to keep the rest of the files
					break;
				}
			}
			if ($DeletedKeys['maxsize']) {
				$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . $this->pluralize($DeletedKeys['maxsize']) . " based on (cache_maxsize={$description['maxsize']})");
				$madedeletions = true;
			}
		}

		$totalpurged = 0;
		foreach ($DeletedKeys as $value) {
			$totalpurged += $value;
		}
		$this->modx->log(modX::LOG_LEVEL_INFO, ':: Purged ' . $this->pluralize($totalpurged) . ($madedeletions ? ' || New cache size: ' . $this->pluralize(count($CacheDirOldFilesSize)) . ' (' . round(array_sum($CacheDirOldFilesSize) / 1048576, 2) . ' MB)': ''));
	}
	$this->modx->log(modX::LOG_LEVEL_INFO, '');
}

}