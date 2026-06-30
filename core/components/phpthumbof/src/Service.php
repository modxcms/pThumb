<?php

namespace MODX\phpThumbOf;

class Service
{
    public $phpThumb;

    protected $modx;
    protected $config;
    protected $cacheImgRegex;

    private $input;

    function __construct(\modX &$modx, $options, $s3info = 0) {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', $options, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', $options, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', $options, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');
        $modxVersion = $this->modx->getVersionData();

        $this->config = array_merge([
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'vendor/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxVersion' => $modxVersion['version'],
            'httpHost' => $modx->getOption('http_host', $options, MODX_HTTP_HOST),
            'useCache' => $this->getOption('ptuse_cache', $options, true),
            'cachePath' => $this->getOption('cache_path', $options, $assetsPath . 'cache'),
            'imagesBasedir' => trim($this->getOption('ptcache_images_basedir', $options, 'assets'), '/') . '/',
            'postfixPropertyHash' => $this->getOption('postfix_property_hash', $options, true),
            'cacheWritable' => true,
            'checkModTime' => $this->getOption('check_mod_time', $options, FALSE),
            'globalDefaults' => $this->getOption('global_defaults', $options, []),
            's3outputMSglobal' => $this->getOption('s3_output', $options, 0),
            's3multiImgGlobal' => $this->getOption('s3_multi_img', $options, false),
            's3cachePath' =>  trim($this->getOption('s3_cache_path', $options, ''), '/') . '/',
        ], $options);

        if ($this->config['useCache']) {
            $this->config['cachePath'] = MODX_BASE_PATH . $this->getOption('ptcache_location', $options, 'assets/image-cache');
        }
        $this->config['cachePath'] = str_replace(array('[[+assets_path]]', '[[+base_path]]'), array(MODX_ASSETS_PATH, MODX_BASE_PATH), $this->config['cachePath']);
        $this->config['cachePath'] = str_replace(array('{{assets_path}}', '{{base_path}}'), array(MODX_ASSETS_PATH, MODX_BASE_PATH), $this->config['cachePath']);
        $this->config['cachePath'] = rtrim(str_replace('//', '/', $this->config['cachePath']), '/') . '/';  // just in case
        if (in_array(
            $this->config['cachePath'],
            ['/', '', MODX_BASE_PATH],
            true
        )) {  
            // for safety, pThumb cache location has to be a subdir, can't be the web root
            $this->config['cachePath'] = $assetsPath . 'cache';
        }
        $basePathRegex = '/^' . preg_quote(MODX_BASE_PATH, '/') . '/';
        $this->config['cacheUrl'] = preg_replace($basePathRegex, '', $this->config['cachePath']);
        $this->config['remoteImagesCachePath'] = $this->config['cachePath'] . 'remote-images/';

        $this->addPackage();

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');

        if (!is_writable($this->config['cachePath']) && !$modx->cacheManager->writeTree($this->config['cachePath'])) {  // check cache writability
            $modx->log(\modX::LOG_LEVEL_ERROR, "[pThumb] Cache path not writable: {$this->config['cachePath']}");
            $this->config['cacheWritable'] = false;
            return;
        }

        if ($s3info) {  // used by the cache cleaner class
            $this->config['s3multiImgGlobal'] = true;
            $this->cacheImgRegex = '/^' . preg_quote($this->config['s3cachePath'], '/') . '.+\.(?:[0-9a-f]{8}|[0-9a-f]{32})\.(?:jpe?g|png|gif|webp|avif|bmp|ico|wbmp)$/';  // for safety, only select images with a hash
        }
        // these can't be cached
        // setup any S3 output media source
        $this->config['s3outputMS'] = (int) ( $options['s3output'] ?? $this->config['s3outputMSglobal']);
        if ( $this->config['s3outputMS'] ) {
            $this->config['s3outKey'] = "s3out{$this->config['s3outputMS']}";
            $s3OutMS = $this->config[$this->config['s3outKey']] ?? false;
            if (!$s3OutMS) {  // if this MS isn't cached already
                $this->config["{$this->config['s3outKey']}_ok"] = false;
                $this->config[$this->config['s3outKey']] = $modx->getObject('modMediaSource', $this->config['s3outputMS']);
                /** @var \modMediaSource $s3obj */
                $s3obj =& $this->config[$this->config['s3outKey']];
                if ($s3obj && !str_contains(get_class($s3obj), 'modS3MediaSource')) {  // check for valid S3 media source
                    $modx->log(\modX::LOG_LEVEL_ERROR, "[pThumb] No such S3 output media source: {$this->config['s3outputMS']}");
                    $this->config['s3outputMS'] = 0;  // prevent any further S3 processing this time through
                    $this->config[$this->config['s3outKey']] = false;
                }
                elseif ($s3obj) {  // initialize MS
                    $this->config["{$this->config['s3outKey']}_ok"] = true;
                    $s3properties = $s3obj->getPropertyList();
                    $this->config["{$this->config['s3outKey']}_url"] = $s3properties['url'];
                }
            }
        }
        $this->config['s3multiImg'] = $options['s3multiImg'] ?? $this->config['s3multiImgGlobal'];
    }


    /*
     *  Write current resource id, image filename and $msg to the MODX error log.
     *  if $phpthumbDebug, also write the phpThumb debugmessages array
     */
    public function debug($msg, $phpthumbDebug = FALSE) {
        $logmsg = '[pThumb] ' . (isset($this->modx->resource) ? "Resource: {$this->modx->resource->get('id')} || " : '') .
            'Image: ' . (isset($this->input) ? $this->input : '(none)') .
            ($msg ? "\n$msg" : '');
        if ($phpthumbDebug && isset($this->phpThumb->debugmessages)) {
            $logmsg .= ($this->config['useResizer'] ? "\nResizer" : "\nphpThumb") .
                ' debug output:' . substr(print_r($this->phpThumb->debugmessages, TRUE), 7, -2) .
                "----------------------\n";
        }
        $this->modx->log(\modX::LOG_LEVEL_DEBUG, $logmsg);
    }


    /*
     *  Create a thumbnail from $src with $options
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
        if (!$this->config['cacheWritable']) {
            return $output;
        }
        if (strtolower(pathinfo($src, PATHINFO_EXTENSION)) === 'svg') { // abort if file is SVG
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

            $localFileIsOutOfDate = false;
            if($this->config['checkModTime'] && file_exists($file)) {
                $curl = curl_init(str_replace(' ', '%20', $src));

                curl_setopt($curl, CURLOPT_NOBODY, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FILETIME, true);

                $result = curl_exec($curl);

                if ($result === false) {
                    $this->modx->log(\modX::LOG_LEVEL_ERROR, "[pThumb] Remote images file modification time could not retrieved: $remoteFilePath");
                } else {
                    $timestamp = curl_getinfo($curl, CURLINFO_FILETIME);

                    if($timestamp > 0 && $timestamp > filemtime($file))
                        $localFileIsOutOfDate = true;
                }
            }


            if (!file_exists($file) || $localFileIsOutOfDate) {  // if it's not in our cache, go get it
                if (!is_writable($remoteFilePath)) {
                    if ( !$this->modx->cacheManager->writeTree($remoteFilePath) ) {
                        $this->modx->log(\modX::LOG_LEVEL_ERROR, "[pThumb] Remote images cache path not writable: $remoteFilePath");
                        return $output;
                    }
                }
                if (!isset($this->config['remoteTimeout'])) {  // first time through set up any additional remote images settings
                    $this->config['remoteTimeout'] = (int) $this->getOption('remote_timeout', $options, 5);  // in seconds. For fetching remote images
                }
                $fh = fopen($file, 'wb');
                if (!$fh) {
                    $this->debug("[pThumb remote images] Unable to write to cache file: $file  *** Skipping ***");
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
                    $this->debug("[pThumb remote images] Retrieving $src\nTarget filename: $file\ncURL error: " . curl_error($ch) . "  *** Skipping ***\n");
                    $curlFail = TRUE;
                }
                curl_close($ch);
                fclose($fh);
                if ($curlFail || !getimagesize($file)) {  // if we didn't get an image, skip and remove from cache
                    $this->debug("[pThumb remote images] Failed to cache $src");
                    unlink($file);
                    return $output;
                }
            }
        }
        else {  // it's a local file
            // see if open_basedir is active - avoid calling is_readable in PHP >= 8.x
            $openBasedirIniSetting = ini_get('open_basedir');
            $isOpenBasedirSafe = true;
            if (is_string($openBasedirIniSetting)) {
                $isOpenBasedirSafe = false;
                $openBasedirPaths = explode(":", $openBasedirIniSetting);
                foreach($openBasedirPaths as $path) {
                    if (substr($src, 0, strlen($path)) == $path) {
                        $isOpenBasedirSafe = true;
                        break;
                    }
                }
            }
            if ($isOpenBasedirSafe && is_readable($src)) {  // if we've already got an existing file, keep going
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
                        $this->debug('File not ' . (file_exists($file) ? 'readable': 'found') . ": $file  *** Skipping ***");
                        return $output;
                    }
                }
            }
            if (is_dir($file)) {
                $this->debug("$file is a directory  *** Skipping ***");
                return $output;
            }
        }
        $this->input = $output['file'] = $file;

        /* Process options. Set $ptOptions */
        if (!is_array($options)) {  // convert options string to array
            parse_str(html_entity_decode($options), $ptOptions);
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
            $ext =  preg_replace('#[^a-z]#', '', strtolower($inputParts['extension']));
            $availableExtensions = ['jpeg', 'png', 'gif', 'bmp', 'ico', 'wbmp', 'webp', 'avif'];
            $ptOptions['f'] = in_array($ext, $availableExtensions, true) ? $ext : 'jpeg';
        }
        $output['outputDims'] = !empty($ptOptions['dims']);
        $ptOptions = array_merge($this->config['globalDefaults'], $ptOptions);


        /* Determine cache filename. Set $cacheKey and $cacheUrl */
        $modflags = (int) $this->config['useResizer'];  // keep cached image from being stale if useResizer changes
        if ($this->config['checkModTime']) {
            $modflags .= filemtime($this->input);
        }
        $cacheFilename = $inputParts['filename'] . '.';
        if ($this->config['useCache']) {
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
        $cacheUrl = "{$this->config['cacheUrl']}$cacheFilenamePrefix" . rawurlencode($cacheFilename);

        /* Look for cached file */
        $s3ok = false;
        if ($this->config['s3outputMS']) {  // check for file in S3 MS
            /* @var \modMediaSource $s3out */
            $s3out =& $this->config[$this->config['s3outKey']];
            $cacheFilenamePrefix = $this->config['s3cachePath'] . $cacheFilenamePrefix;
            $s3cacheUrl = $this->config["{$this->config['s3outKey']}_url"] . $cacheFilenamePrefix . rawurlencode($cacheFilename);
            $cacheFilename = "$cacheFilenamePrefix$cacheFilename";
            if ($this->config["{$this->config['s3outKey']}_ok"]) {  // otherwise check individual object
                $s3ok = true;
                $output['success'] = $s3out->getObjectContents($cacheFilename);
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
            if ($this->config['useCache'] && !is_writable($cacheFilenamePath)) {  // make sure pThumb cache location exists
                if ( !$this->modx->cacheManager->writeTree($cacheFilenamePath) ) {
                    $this->modx->log(\modX::LOG_LEVEL_ERROR, "[pThumb] Cache path not writable: $cacheFilenamePath");
                    return $output;
                }
            }

            if (!class_exists('phpthumb', FALSE)) {
                if (!$this->modx->loadClass('phpthumb', MODX_CORE_PATH . 'model/phpthumb/', true, true)) {
                    $this->debug('Could not load phpthumb class.');
                    return $output;
                }
            }
            if (!isset($this->config['modphpthumb'])) {  // make sure we get a few relevant system settings
                $this->config['modphpthumb'] = array();
                $this->config['modphpthumb']['config_allow_src_above_docroot'] = (boolean) $this->modx->getOption('phpthumb_allow_src_above_docroot', $options, false);
                $this->config['modphpthumb']['zc'] = $this->modx->getOption('phpthumb_zoomcrop', $options, 0);
                $this->config['modphpthumb']['far'] = $this->modx->getOption('phpthumb_far', $options, 'C');
                $this->config['modphpthumb']['config_ttf_directory'] = MODX_CORE_PATH . 'model/phpthumb/fonts/';
                $this->config['modphpthumb']['config_document_root'] = $this->modx->getOption('phpthumb_document_root', $options, '');
            }
            $this->phpThumb = new \phpthumb();  // unfortunately we have to create a new object for each image!
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
                $this->debug('Could not generate thumbnail', TRUE);
                return $output;
            }
            $output['success'] = $this->phpThumb->RenderToFile($cacheKey);
        }

        if ($output['success']) {
            $output['src'] = $cacheUrl;
            $output['file'] = $cacheKey;
            if ($s3ok) {  // write to S3
                $output['success'] = $s3out->createObject($cacheUrl, $cacheKey, file_get_contents($cacheKey));
            } else {
                if (!isset($this->config['newFilePermissions'])) {
                    $this->config['newFilePermissions'] = octdec($this->modx->getOption('new_file_permissions', $options, '0664'));
                }
                chmod($cacheKey, $this->config['newFilePermissions']);  // make sure file permissions are correct
            }
        }
        else { $this->debug("Could not cache thumbnail to file at: {$cacheKey}", TRUE); }
        return $output;
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("$this->namespace.$key", $this->modx->config)) {
                $option = $this->modx->getOption("$this->namespace.$key");
            }
        }
        return $option;
    }

    private function addPackage()
    {
    }
}