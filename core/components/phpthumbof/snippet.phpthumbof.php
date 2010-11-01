<?php
/**
 * A custom output filter for phpThumb
 *
 * @package phpthumbof
 */
if (!$modx->loadClass('modPhpThumb',$modx->getOption('core_path').'model/phpthumb/',true,true)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'Could not load modPhpThumb class.');
    return '';
}
$phpThumb = new modPhpThumb($modx,$scriptProperties);
/* do initial setup */
$phpThumb->initialize();
$phpThumb->config_cache_directory = $modx->getOption('phpthumbof.assets_path',$scriptProperties,$modx->getOption('assets_path').'components/phpthumb/').'cache/';

/* get absolute url */
if (strpos($input,'/') !== 0) {
    $input = $modx->getOption('base_url').$input;
}

/* set source and generate thumbnail */
$phpThumb->set($input);

/* check to see if there's a cached file of this already */
if ($phpThumb->checkForCachedFile()) {
    $phpThumb->loadCache();
    return '';
}

/* generate thumbnail */
$phpThumb->generate();

/* cache the thumbnail and output */
$phpThumb->cache();

return $phpThumb->cache_filename;
//$phpThumb->output();