<?php
/**
 * pThumb
 * Copyright 2013 Jason Grant
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
 */

class phpThumbOf {

public $phpThumb;
public $cacheWritable = TRUE;

protected $modx;
protected $config;

private $input;

function __construct(modX &$modx, &$settings_cache, $options = array()) {
	$this->modx =& $modx;
	$this->config =& $settings_cache;
	if (empty($this->config)) {  // first time through, get and store all the settings
		$this->config['assetsPath'] = $modx->getOption('assets_path', null, MODX_ASSETS_PATH);
		if ( $this->config['use_ptcache'] = $modx->getOption('pthumb.use_ptcache', null, TRUE) ) {
			$this->config['cachePath'] = MODX_BASE_PATH . $modx->getOption('pthumb.ptcache_location', null, 'assets/image-cache', TRUE);
			$this->config['imagesBasedir'] = trim($modx->getOption('pthumb.ptcache_images_basedir', null, 'assets'), '/') . '/';
			$this->config['imagesBasedirLen'] = strlen($this->config['imagesBasedir']);
		}
		else {
			$this->config['cachePath'] = $modx->getOption('phpthumbof.cache_path', null, "{$this->config['assetsPath']}components/phpthumbof/cache", TRUE);
			$this->config['cachePath'] = str_replace(array('[[+assets_path]]', '[[+base_path]]'), array($this->config['assetsPath'], MODX_BASE_PATH), $this->config['cachePath']);
			$this->config['postfixPropertyHash'] = $modx->getOption('phpthumbof.postfix_property_hash', null, TRUE);
		}
		$this->config['cachePath'] = rtrim(str_replace('//', '/', $this->config['cachePath']), '/') . '/';  // just in case
		if (!is_writable($this->config['cachePath']) && !$modx->cacheManager->writeTree($this->config['cachePath'])) {  // check cache writability
			$modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Cache path not writable: {$this->config['cachePath']}");
			$this->cacheWritable = FALSE;
			return;
		}
		$cacheurl = rtrim($modx->getOption('phpthumbof.cache_url', null, MODX_BASE_URL), '/');
		$this->config['cachePathUrl'] = str_replace(MODX_BASE_PATH, "$cacheurl/", $this->config['cachePath']);
		$this->config['remoteImagesCachePath'] = "{$this->config['assetsPath']}components/phpthumbof/cache/remote-images/";
		$this->config['basePathCheck'] = MODX_BASE_PATH . ltrim(MODX_BASE_URL, '/');  // used to weed out duplicate subdirs
		$this->config['checkModTime'] = $modx->getOption('phpthumbof.check_mod_time', null, FALSE);
		parse_str($modx->getOption('pthumb.global_defaults', null, ''), $this->config['globalDefaults']);
		$this->config['useResizerGlobal'] = $modx->getOption('phpthumbof.use_resizer', null, FALSE);
	}
	// these two can't be cached
	$this->config['debug'] = empty($options['debug']) ? FALSE : TRUE;
	$this->config['useResizer'] = isset($options['useResizer']) ? $options['useResizer'] : $this->config['useResizerGlobal'];
}


/*
 *  Write current resource id, image filename and $msg to the MODX error log.
 *  if $phpthumbDebug, also write the phpThumb debugmessages array
 */
public function debugmsg($msg, $phpthumbDebug = FALSE) {
	$logmsg = "[pThumb] Resource: {$this->modx->resource->get('id')} || Image: " .
		(isset($this->input) ? $this->input : '(none)') .
		($msg ? "\n$msg" : '');
	if ($phpthumbDebug && isset($this->phpThumb->debugmessages)) {
		$logmsg .= ($this->config['useResizer'] ? "\nResizer" : "\nphpThumb") .
			' debug output:' . substr(print_r($this->phpThumb->debugmessages, TRUE), 7, -2) .
			"----------------------\n";
	}
	$this->modx->log(modX::LOG_LEVEL_ERROR, $logmsg);
}


/*
 *  Create a thumnail from $src with $options
 *  $src can be a path/filename or URL and absolute or relative
 *  Returns the filename of the cached image on success or $src on failure
 */
public function createThumbnail($src, $options) {
	$isRemote = preg_match('/^(?:https?:)?\/\/((?:.+?)\.(?:.+?))\/(.+)/i', $src, $matches);  // check for absolute URLs
	if ($isRemote && MODX_HTTP_HOST === strtolower($matches[1])) {  // if it's the same server we're running on
		$isRemote = FALSE;  // then it's not really remote
		$src = $matches[2];  // we just need the path and filename
	}
	if ($isRemote) {  // if we've got a real remote image to work with
		$file = $this->config['remoteImagesCachePath'] . preg_replace('/[^\w\d\-_\.]/', '-', "{$matches[1]}-{$matches[2]}");  // generate a cache filename
		if (!file_exists($file)) {  // if it's not in our cache, go get it
			if (!isset($this->config['remoteTimeout'])) {  // first time through check remote images cache exists and is writable
				if (!is_writable($this->config['remoteImagesCachePath']) && !$this->modx->cacheManager->writeTree($this->config['remoteImagesCachePath'])) {
					$this->modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Remote images cache path not writable: {$this->config['remoteImagesCachePath']}");
					return $src;
				}
				$this->config['remoteTimeout'] = (int) $this->modx->getOption('phpthumbof.remote_timeout', null, 5);  // in seconds. For fetching remote images
			}
			$fh = fopen($file, 'wb');
			if (!$fh) {
				$this->debugmsg("[pThumb remote images] Unable to write to cache file: $file  *** Skipping ***");
				return $src;
			}
			$curlFail = FALSE;
			if ($src[0] === '/') {  //cURL doesn't like protocol-relative URLs, so add http or https
				$src = (empty($_SERVER['HTTPS']) ? 'http:' : 'https:') . $src;
			}
			$ch = curl_init($src);
			curl_setopt_array($ch, array(
				CURLOPT_TIMEOUT	=> $this->config['remoteTimeout'],
				CURLOPT_FILE => $fh,
				CURLOPT_FAILONERROR => TRUE
			));
			curl_exec($ch);  // download the file and store it in $fh
			if (curl_errno($ch)) {
				$this->debugmsg("[pThumb remote images] Retrieving $src\nTarget filename: $file\ncURL error: " . curl_error($ch) . "  *** Skipping ***\n");
				$curlFail = TRUE;
			}
			curl_close($ch);
			fclose($fh);
			if ($curlFail || !filesize($file)) {  // if we didn't get it, skip and don't cache
				$this->debugmsg("[pThumb remote images] Failed to cache $src");
				unlink($file);
				return $src;
			}
		}
	}
	else {  // it's a local file
		if (is_readable($src)) {  // if we've already got an existing file, keep going
			$file = $src;
		}
		else {  // otherwise prepend basePath and try again
			$file = MODX_BASE_PATH . rawurldecode(ltrim($src, '/'));  // Fix spaces and other encoded characters in the filename
			$file = str_replace($this->config['basePathCheck'], MODX_BASE_PATH, $file);  // if MODX is in a subdir, keep this subdir name from occuring twice
			if (!is_readable($file)) {
				$this->debugmsg('File not ' . (file_exists($file) ? 'readable': 'found') . ": $file  *** Skipping ***");
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
	$inputParts = pathinfo($this->input);
	if (empty($ptOptions['f'])) {  // if filetype isn't already set, set it based on extension
		$ext = strtolower($inputParts['extension']);
		$ptOptions['f'] = ($ext === 'png' || $ext === 'gif') ? $ext : 'jpeg';
	}
	$ptOptions = array_merge($this->config['globalDefaults'], $ptOptions);


	/* Determine cache filename. Set $cacheKey and $cacheUrl */
	$modflags = (int) $this->config['useResizer'];  // keep cached image from being stale if useResizer changes
	if ($this->config['checkModTime']) {
		$modflags .= @filemtime($this->input);
	}
	$cacheFilename = "{$inputParts['filename']}.";
	if ($this->config['use_ptcache']) {
		$inputParts['dirname'] .= '/';
		$baseDirOffset = strpos($inputParts['dirname'], $this->config['imagesBasedir']);
		if ($baseDirOffset === FALSE) {  // not coming from imagesBasedir, so throw it in the top level of the cache
			$cacheFilenamePrefix = '';
		}
		else {  // trim off everything before and including imagesBasedir
			$cacheFilenamePrefix = substr($inputParts['dirname'], $baseDirOffset + $this->config['imagesBasedirLen']);
		}
		$cacheFilename .= hash('crc32', $modflags . json_encode($ptOptions)) . '.';
		$cacheFilenamePath = "{$this->config['cachePath']}$cacheFilenamePrefix";
	}
	else {  // use classic phpThumbOf cache
		$cacheFilenamePrefix = '';
		if ($this->config['postfixPropertyHash']) {
			$cacheFilename .= md5("$modflags{$inputParts['dirname']}" . json_encode($ptOptions)) . '.';
		}
	}
	$cacheFilename .= $ptOptions['f'] === 'jpeg' ? 'jpg' : $ptOptions['f'];  // extension
	$cacheKey = "{$this->config['cachePath']}$cacheFilenamePrefix$cacheFilename";
	$cacheUrl = "{$this->config['cachePathUrl']}$cacheFilenamePrefix" . rawurlencode($cacheFilename);

	if (file_exists($cacheKey)) {  // If the file's in the cache, we're done.
		return $cacheUrl;
	}

	if ($this->config['use_ptcache'] && !is_writable($cacheFilenamePath)) {
		if ( !$this->modx->cacheManager->writeTree($cacheFilenamePath) ) {
			$this->modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Cache path not writable: $cacheFilenamePath");
			return $src;
		}
	}

	if ($this->config['useResizer']) {
		static $resizer_obj = array();
		if (!class_exists('Resizer')) {  // set up Resizer. We'll reuse this object for any subsequent images on the page
			if (!$this->modx->loadClass('Resizer', MODX_CORE_PATH . 'components/resizer/model/', true, true)) {
				$this->debugmsg('Could not load Resizer class.');
				return $src;
			}
			$resizer_obj[0] = new Resizer($this->modx);  // we'll reuse this same object for all subsequent images
			$resizer_obj[0]->debug = $this->config['debug'];
		}
		elseif ($this->config['debug'])  {  // We've already got a Resizer object and will just clear out its debug log
			$resizer_obj[0]->resetDebug();
		}
		$this->phpThumb = $resizer_obj[0];
		$writeSuccess = $this->phpThumb->processImage($this->input, $cacheKey, $ptOptions);
	}
	else {  //use phpThumb
		if (!class_exists('phpthumb', FALSE)) {
			if (!$this->modx->loadClass('phpthumb', MODX_CORE_PATH . 'model/phpthumb/', true, true)) {
				$this->debugmsg('Could not load phpthumb class.');
				return $src;
			}
		}
		if (!isset($this->config['modphpthumb'])) {  // make sure we get a few relevant system settings
			$this->config['modphpthumb'] = array();
			$this->config['modphpthumb']['config_allow_src_above_docroot'] = (boolean) $this->modx->getOption('phpthumb_allow_src_above_docroot', null, false);
			$this->config['modphpthumb']['zc'] = $this->modx->getOption('phpthumb_zoomcrop', null, 0);
			$this->config['modphpthumb']['far'] = $this->modx->getOption('phpthumb_far', null, 'C');
			$this->config['modphpthumb']['config_ttf_directory'] = $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'model/phpthumb/fonts/';
			$this->config['modphpthumb']['config_document_root'] = $this->modx->getOption('phpthumb_document_root', null, '');
		}
		$this->phpThumb = new phpthumb($this->modx);  // unfortunately we have to create a new object for each image!
		foreach ($this->config['modphpthumb'] as $param => $value) {  // add MODX system settings
			$this->phpThumb->$param = $value;
		}
		foreach ($ptOptions as $param => $value) {  // add options passed to the snippet
			$this->phpThumb->setParameter($param, $value);
		}
		// try to avert problems when $_SERVER['DOCUMENT_ROOT'] is different than MODX_BASE_PATH
		if (!$this->phpThumb->config_document_root) {
			$this->phpThumb->config_document_root = MODX_BASE_PATH;  // default if nothing set from system settings
		}
		$this->phpThumb->config_cache_directory = "{$this->config['cachePath']}$cacheFilenamePrefix";  // doesn't matter, but saves phpThumb some frustration
		$this->phpThumb->setSourceFilename($this->input[0] === '/' ? $this->input : MODX_BASE_PATH . $this->input);

		if (!$this->phpThumb->GenerateThumbnail()) {  // create the thumbnail
			$this->debugmsg('Could not generate thumbnail', TRUE);
			return $src;
		}
		$writeSuccess = $this->phpThumb->RenderToFile($cacheKey);
	}

	if ($writeSuccess) {  // write it to the cache file
		if (!isset($this->config['newFilePermissions'])) {
			$this->config['newFilePermissions'] = octdec($this->modx->getOption('new_file_permissions', null, '0664'));
		}
		@chmod($cacheKey, $this->config['newFilePermissions']);  // make sure file permissions are correct
		return $cacheUrl;
	}
	else {
		$this->debugmsg("Could not cache thumbnail to file at: {$cacheKey}", TRUE);
		return $src;
	}
}


}