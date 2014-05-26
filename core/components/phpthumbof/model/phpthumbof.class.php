<?php
/**
 * pThumb
 * Copyright 2013, 2014 Jason Grant
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

protected $modx;
protected $config;
protected $cacheimgRegex;

private $input;

function __construct(modX &$modx, &$settings_cache, $options, $s3info = 0) {
	$this->modx =& $modx;
	$this->config =& $settings_cache;
	if (empty($this->config)) {  // first time through, get and store all the settings
		$this->config['assetsPath'] = $modx->getOption('assets_path', null, MODX_ASSETS_PATH);
		$this->config['httpHost'] = $modx->getOption('http_host', null, MODX_HTTP_HOST);
		if ( $this->config['use_ptcache'] = $modx->getOption('pthumb.use_ptcache', null, TRUE) ) {
			$this->config['cachePath'] = $modx->getOption('pthumb.ptcache_location', null, 'assets/image-cache', TRUE);
			if ($this->config['cachePath'] === '/') {  // for safety, pThumb cache location has to be a subdir, can't be the web root
				$this->config['cachePath'] = 'assets/image-cache';
			}
			$this->config['cachePath'] = MODX_BASE_PATH . $this->config['cachePath'];
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
			$this->config['cacheNotWritable'] = true;
			return;
		}
		$this->config['cacheNotWritable'] = false;
		$cacheurl = rtrim($modx->getOption('phpthumbof.cache_url', null, $modx->getOption('base_url', null, MODX_BASE_URL), true), '/');
		$this->config['cachePathUrl'] = str_replace(MODX_BASE_PATH, "$cacheurl/", $this->config['cachePath']);
		$this->config['remoteImagesCachePath'] = "{$this->config['assetsPath']}components/phpthumbof/cache/remote-images/";
		$this->config['checkModTime'] = $modx->getOption('phpthumbof.check_mod_time', null, FALSE);
		parse_str($modx->getOption('pthumb.global_defaults', null, ''), $this->config['globalDefaults']);
		$this->config['useResizerGlobal'] = $modx->getOption('phpthumbof.use_resizer', null, FALSE);
		$this->config['s3outputMSglobal'] = $modx->getOption('pthumb.s3_output', null, 0, true);
		if ( $this->config['s3cachePath'] = trim($modx->getOption('pthumb.s3_cache_path', null, ''), '/') ) {
			$this->config['s3cachePath'] .= '/';  // only added if the string isn't empty
		}
		$this->config['s3multiImgGlobal'] = $s3info ? true : $modx->getOption('pthumb.s3_multi_img', null, false);
		if ($s3info) {  // used by the cache cleaner class
			$this->cacheimgRegex = '/^' . str_replace('/', '\/', $this->config['s3cachePath']) . '.+\.(?:[0-9a-f]{8}|[0-9a-f]{32})\.(?:jpe?g|png|gif)$/';  // for safety, only select images with a hash
		}
	}
	// these can't be cached
	$this->config['debug'] = empty($options['debug']) ? FALSE : TRUE;
	$this->config['useResizer'] = isset($options['useResizer']) ? $options['useResizer'] : $this->config['useResizerGlobal'];
	// setup any S3 output media source
	if ( $this->config['s3outputMS'] = (int) (isset($options['s3output']) ? $options['s3output'] : $this->config['s3outputMSglobal']) ) {
		$this->config['s3outKey'] = "s3out{$this->config['s3outputMS']}";
		if (!isset($this->config[$this->config['s3outKey']])) {  // if this MS isn't cached already
			$this->config["{$this->config['s3outKey']}_ok"] = false;
			$this->config[$this->config['s3outKey']] = $modx->getObject('modMediaSource', $this->config['s3outputMS']);
			$s3obj =& $this->config[$this->config['s3outKey']];
			if (strpos(get_class($s3obj), 'modS3MediaSource') === false) {  // check for valid S3 media source
				$modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] No such S3 output media source: {$this->config['s3outputMS']}");
				$this->config['s3outputMS'] = 0;  // prevent any further S3 processing this time through
				$this->config[$this->config['s3outKey']] = false;
			}
			else {  // initialize MS
				$this->config["{$this->config['s3outKey']}_ok"] = true;
				$s3properties = $s3obj->getPropertyList();
				$this->config["{$this->config['s3outKey']}_url"] = $s3properties['url'];
				$s3obj->bucket = $s3properties['bucket'];
				include_once MODX_CORE_PATH . 'model/aws/sdk.class.php';
				define('AWS_KEY', $s3properties['key']);
				define('AWS_SECRET_KEY', $s3properties['secret_key']);
				try { $s3obj->driver = new AmazonS3(); }
				catch (Exception $e) {
					$modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Error connecting to S3 media source {$this->config['s3outputMS']}: " . $e->getMessage());
					$this->config["{$this->config['s3outKey']}_ok"] = false;
					$this->config['s3outputMS'] = 0;
				}
			}
		}
	}
	$this->config['s3multiImg'] = isset($options['s3multiImg']) ? $options['s3multiImg'] : $this->config['s3multiImgGlobal'];
	if ($this->config['s3outputMS'] && $this->config["{$this->config['s3outKey']}_ok"] && $this->config['s3multiImg'] && !isset($this->config[$this->config['s3outKey'] . '_images'])) {  // get a list of all objects in the bucket
		$s3obj =& $this->config[$this->config['s3outKey']];
		$opt = array();
		$objects = array();
		do {  // list_objects only gets 1000 objects at a time, so we'll loop if necessary
			$list = $s3obj->driver->list_objects($s3obj->bucket, $opt);
			if (is_string($list->body)) {
				$list->body = new CFSimpleXML($list->body);
			}
			if ($s3info) {  // also store last modified time and file size
				foreach ($list->body->Contents as $obj) {
					$key = (string) $obj->Key;
					if (preg_match($this->cacheimgRegex, $key)) {
						$objects[$key] = array(
							'mod' => strtotime($obj->LastModified),
							'size' => (int) $obj->Size
						);
					}
				}
			}
			elseif ( $keys = $list->body->query('descendant-or-self::Key')->map_string(null) ) {  // otherwise just get object names
				$objects = array_merge($objects, $keys);
			}
			$body = (array) $list->body;
			$opt = array('marker' => (isset($body['Contents']) && is_array($body['Contents'])) ? ((string) end($body['Contents'])->Key) : ((string) $list->body->Contents->Key));  // set starting point for next request
		} while ((string) $list->body->IsTruncated === 'true');
		$this->config[$this->config['s3outKey'] . '_images'] = $objects;
		unset($objects);
	}
}


/*
 *  Write current resource id, image filename and $msg to the MODX error log.
 *  if $phpthumbDebug, also write the phpThumb debugmessages array
 */
public function debugmsg($msg, $phpthumbDebug = FALSE) {
	$logmsg = '[pThumb] ' . (isset($this->modx->resource) ? "Resource: {$this->modx->resource->get('id')} || " : '') .
		'Image: ' . (isset($this->input) ? $this->input : '(none)') .
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
	$src = str_replace('/./', '/', $src);  // get rid of any /./ instances in the path
	$output = array(
		'src' => $src,
		'file' => '',
		'width' => '',
		'height' => '',
		'outputDims' => false,
		'success' => false
	);
	if ($this->config['cacheNotWritable']) {
		return $output;
	}
/* Find input file */
	$isRemote = preg_match('/^(?:https?:)?\/\/((?:.+?)\.(?:.+?))\/(.+)/i', $src, $matches);  // check for absolute URLs
	if ($isRemote && $this->config['httpHost'] === strtolower($matches[1])) {  // if it's the same server we're running on
		$isRemote = false;  // then it's not really remote
		$src = $matches[2];  // we just need the path and filename
	}
	if ($isRemote) {  // if we've got a real remote image to work with
		$hashExtras = $matches[1];  // we'll put the remote site name into the hash later
		$remoteUrl = explode('?', $matches[2]);  // break off any query string
		$remoteUrl[0] = rawurldecode($remoteUrl[0]);  // just in case?
		$inputParts = pathinfo($remoteUrl[0]);
		$inputParts['dirname'] = $inputParts['dirname'] === '.' ? '' : "{$inputParts['dirname']}/";  // remove '.' if in top level dir
		$cachebuster = '.';
		if (isset($remoteUrl[1])) {
			$hashExtras .= $remoteUrl[1];
			$cachebuster .= hash('crc32', $remoteUrl[1]) . '.';
		}
		$remoteCacheName = "{$inputParts['filename']}$cachebuster{$inputParts['extension']}";  // hash any query string to allow for cache busting
		$remoteFilePath = "{$this->config['remoteImagesCachePath']}{$matches[1]}/{$inputParts['dirname']}";
		$file = "$remoteFilePath$remoteCacheName";
		if (!file_exists($file)) {  // if it's not in our cache, go get it
			if (!is_writable($remoteFilePath)) {
				if ( !$this->modx->cacheManager->writeTree($remoteFilePath) ) {
					$this->modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Remote images cache path not writable: $remoteFilePath");
					return $output;
				}
			}
			if (!isset($this->config['remoteTimeout'])) {  // first time through set up any additional remote images settings
				$this->config['remoteTimeout'] = (int) $this->modx->getOption('phpthumbof.remote_timeout', null, 5);  // in seconds. For fetching remote images
			}
			$fh = fopen($file, 'wb');
			if (!$fh) {
				$this->debugmsg("[pThumb remote images] Unable to write to cache file: $file  *** Skipping ***");
				return $output;
			}
			$curlFail = FALSE;
			if ($src[0] === '/') {  //cURL doesn't like protocol-relative URLs, so add http or https
				$src = (empty($_SERVER['HTTPS']) ? 'http:' : 'https:') . $src;
			}
			$ch = curl_init(str_replace(' ', '%20', $src));
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
			if ($curlFail || !getimagesize($file)) {  // if we didn't get an image, skip and remove from cache
				$this->debugmsg("[pThumb remote images] Failed to cache $src");
				unlink($file);
				return $output;
			}
		}
	}
	else {  // it's a local file
		if (is_readable($src)) {  // if we've already got an existing file, keep going
			$file = $src;
		}
		else {  // otherwise prepend base_path and try again
			$file = MODX_BASE_PATH . rawurldecode(ltrim($src, '/'));  // Fix spaces and other encoded characters in the filename
			if (!is_readable($file)) {  // still can't find it?  We'll try to correct a couple common problems.
				if (!isset($this->config['basePathCheck'])) {
					$this->config['basePathCheck'] = MODX_BASE_PATH . ltrim($this->modx->getOption('base_url'), '/');
				}
				$file = str_replace($this->config['basePathCheck'], MODX_BASE_PATH, $file);  // if MODX is in a subdir, keep this subdir name from occuring twice. Also remove base_url, which might just be added by a context
				if (!is_readable($file)) {  // Time to declare failure
					$this->debugmsg('File not ' . (file_exists($file) ? 'readable': 'found') . ": $file  *** Skipping ***");
					return $output;
				}
			}
		}
		if (is_dir($file)) {
			$this->debugmsg("$file is a directory  *** Skipping ***");
			return $output;
		}
	}
	$this->input = $output['file'] = $file;

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
	if (!$isRemote) {  // remote stuff has already been set up above
		$inputParts = pathinfo($this->input);
		$hashExtras = '';
	}
	if (empty($ptOptions['f'])) {  // if filetype isn't already set, set it based on extension
		$ext = strtolower($inputParts['extension']);
		$ptOptions['f'] = ($ext === 'png' || $ext === 'gif') ? $ext : 'jpeg';
	}
	$output['outputDims'] = !empty($ptOptions['dims']);
	$ptOptions = array_merge($this->config['globalDefaults'], $ptOptions);


/* Determine cache filename. Set $cacheKey and $cacheUrl */
	$modflags = (int) $this->config['useResizer'];  // keep cached image from being stale if useResizer changes
	if ($this->config['checkModTime']) {
		$modflags .= filemtime($this->input);
	}
	$cacheFilename = $inputParts['filename'] . '.';
	if ($this->config['use_ptcache']) {
		if ($isRemote) {
			$cacheFilenamePrefix = $inputParts['dirname'];
		}
		else {
			$inputParts['dirname'] .= '/';
			$baseDirOffset = strpos($inputParts['dirname'], $this->config['imagesBasedir']);
			if ($baseDirOffset === false) {  // not coming from imagesBasedir, so throw it in the top level of the cache
				$cacheFilenamePrefix = '';
			}
			else {  // trim off everything before and including imagesBasedir
				$cacheFilenamePrefix = substr($inputParts['dirname'], $baseDirOffset + $this->config['imagesBasedirLen']);
			}
		}
		$cacheFilenamePath = "{$this->config['cachePath']}$cacheFilenamePrefix";
		$cacheFilename .= hash('crc32', $modflags . json_encode($ptOptions) . $hashExtras) . '.';
	}
	else {  // use classic phpThumbOf cache
		$cacheFilenamePrefix = '';
		if ($this->config['postfixPropertyHash']) {
			$cacheFilename .= md5("$modflags{$inputParts['dirname']}" . json_encode($ptOptions) . $hashExtras) . '.';
		}
	}
	$cacheFilename .= $ptOptions['f'] === 'jpeg' ? 'jpg' : $ptOptions['f'];  // extension
	$cacheKey = "{$this->config['cachePath']}$cacheFilenamePrefix$cacheFilename";
	$cacheUrl = "{$this->config['cachePathUrl']}$cacheFilenamePrefix" . rawurlencode($cacheFilename);

/* Look for cached file */
	$s3ok = false;
	if ($this->config['s3outputMS']) {  // check for file in S3 MS
		$s3out =& $this->config[$this->config['s3outKey']];
		$cacheFilenamePrefix = $this->config['s3cachePath'] . $cacheFilenamePrefix;
		$s3cacheUrl = $this->config["{$this->config['s3outKey']}_url"] . $cacheFilenamePrefix . rawurlencode($cacheFilename);
		$cacheFilename = "$cacheFilenamePrefix$cacheFilename";
		if (isset($this->config[$this->config['s3outKey'] . '_images'])) {  // we have a list of all objects in the bucket
			$s3ok = true;
			$output['success'] = in_array($cacheFilename, $this->config[$this->config['s3outKey'] . '_images'], true);
		}
		elseif ($this->config["{$this->config['s3outKey']}_ok"]) {  // otherwise check individual object
			$s3ok = true;
			$output['success'] = $s3out->driver->if_object_exists($s3out->bucket, $cacheFilename);
		}
	}
	if (file_exists($cacheKey)) {
		$output['file'] = $cacheKey;
		if (!$s3ok) {  // thumbnail in local cache, not using S3 or S3 didn't initialize
			$output['success'] = true;
			$output['src'] = $cacheUrl;
			return $output;
		}
		elseif ($output['success']) {  // thumbnail in both local and S3 caches
			$output['src'] = $s3cacheUrl;
			return $output;
		}
		$output['success'] = true;
	}
	elseif ($output['success']) {  // thumbnail on S3, but not in local cache
		$output['file'] = '';
		$output['src'] = $s3cacheUrl;
		return $output;
	}
	else {
/* Generate Thumbnail */
		if ($this->config['use_ptcache'] && !is_writable($cacheFilenamePath)) {  // make sure pThumb cache location exists
			if ( !$this->modx->cacheManager->writeTree($cacheFilenamePath) ) {
				$this->modx->log(modX::LOG_LEVEL_ERROR, "[pThumb] Cache path not writable: $cacheFilenamePath");
				return $output;
			}
		}

		if ($this->config['useResizer']) {  // use Resizer
			static $resizer_obj = array();
			if (!class_exists('Resizer')) {  // set up Resizer. We'll reuse this object for any subsequent images on the page
				if (!$this->modx->loadClass('Resizer', MODX_CORE_PATH . 'components/resizer/model/', true, true)) {
					$this->debugmsg('Could not load Resizer class.');
					return $output;
				}
				$resizer_obj[0] = new Resizer($this->modx);  // we'll reuse this same object for all subsequent images
				$resizer_obj[0]->debug = $this->config['debug'];
			}
			else {  // We've already got a Resizer object and will just clear out its debug log
				$resizer_obj[0]->resetDebug();
			}
			$this->phpThumb = $resizer_obj[0];
			$output['success'] = $this->phpThumb->processImage($this->input, $cacheKey, $ptOptions);
			if ($output['success']) {
				$output['width'] = $this->phpThumb->width;
				$output['height'] = $this->phpThumb->height;
			}
		}
		else {  // use phpThumb
			if (!class_exists('phpthumb', FALSE)) {
				if (!$this->modx->loadClass('phpthumb', MODX_CORE_PATH . 'model/phpthumb/', true, true)) {
					$this->debugmsg('Could not load phpthumb class.');
					return $output;
				}
			}
			if (!isset($this->config['modphpthumb'])) {  // make sure we get a few relevant system settings
				$this->config['modphpthumb'] = array();
				$this->config['modphpthumb']['config_allow_src_above_docroot'] = (boolean) $this->modx->getOption('phpthumb_allow_src_above_docroot', null, false);
				$this->config['modphpthumb']['zc'] = $this->modx->getOption('phpthumb_zoomcrop', null, 0);
				$this->config['modphpthumb']['far'] = $this->modx->getOption('phpthumb_far', null, 'C');
				$this->config['modphpthumb']['config_ttf_directory'] = MODX_CORE_PATH . 'model/phpthumb/fonts/';
				$this->config['modphpthumb']['config_document_root'] = $this->modx->getOption('phpthumb_document_root', null, '');
			}
			$this->phpThumb = new phpthumb();  // unfortunately we have to create a new object for each image!
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
			$this->phpThumb->setSourceFilename(($this->input[0] === '/' || $this->input[1] === ':') ? $this->input : MODX_BASE_PATH . $this->input);

			if (!$this->phpThumb->GenerateThumbnail()) {  // create the thumbnail
				$this->debugmsg('Could not generate thumbnail', TRUE);
				return $output;
			}
			$output['success'] = $this->phpThumb->RenderToFile($cacheKey);
		}
		if ($output['success']) {
			$output['file'] = $cacheKey;
			if (!isset($this->config['newFilePermissions'])) {
				$this->config['newFilePermissions'] = octdec($this->modx->getOption('new_file_permissions', null, '0664'));
			}
			chmod($cacheKey, $this->config['newFilePermissions']);  // make sure file permissions are correct
		}
	}

	if ($output['success']) {
		$output['src'] = $cacheUrl;
		if ($s3ok) {  // write to S3
			if (!isset($this->config['s3headers'])) {  // first time through set up additional headers
				$this->config['s3headers'] = array();
				$s3headers = explode("\n", $this->modx->getOption('pthumb.s3_headers', null, ''));
				foreach ($s3headers as $header) {
					$header = explode(':', $header);
					if (isset($header[1])) {
						$this->config['s3headers'][trim($header[0])] = trim($header[1]);
					}
				}
			}
			$s3response = $s3out->driver->create_object($s3out->bucket, $cacheFilename, array(
				'fileUpload' => $cacheKey,
				'acl' => AmazonS3::ACL_PUBLIC,
				'headers' => $this->config['s3headers']
			));
			if ($s3response->isOK()) {
				if (isset($this->config[$this->config['s3outKey'] . '_images'])) {
					$this->config[$this->config['s3outKey'] . '_images'][] = $cacheFilename;
				}
				$output['src'] = $s3cacheUrl;
			}
			else { $this->debugmsg("Error uploading $cacheFilename to S3 bucket {$s3out->bucket} (media source {$this->config['s3outputMS']})"); }
		}
	}
	else { $this->debugmsg("Could not cache thumbnail to file at: {$cacheKey}", TRUE); }
	return $output;
}


}
