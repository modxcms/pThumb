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
$assetsPath = $modx->getOption('phpthumbof.assets_path',$scriptProperties,$modx->getOption('assets_path').'components/phpthumb/');
$phpThumb = new modPhpThumb($modx,$ptOptions);

/* do initial setup */
$phpThumb->initialize();
$phpThumb->setParameter('config_cache_directory',$assetsPath.'cache/');
$phpThumb->setParameter('config_allow_src_above_phpthumb',true);
$phpThumb->setParameter('allow_local_http_src',true);
$phpThumb->setCacheDirectory();

/* get absolute url */
if (strpos($input,'/') != 0 && strpos($input.'http') != 0) {
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
$assetsUrl = $modx->getOption('phpthumbof.assets_url',$scriptProperties,$modx->getOption('assets_url').'components/phpthumb/');
$cacheUrl = $assetsUrl.'cache/'.str_replace($phpThumb->config_cache_directory,'',$cacheKey);
$cacheUrl = str_replace('//','/',$cacheUrl);

/* check to see if there's a cached file of this already */
if (file_exists($cacheKey)) {
    return $cacheUrl;
}

/* actually make the thumbnail */
if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
    if ($phpThumb->RenderToFile($cacheKey)) {
        return $cacheUrl;
    }
}
return 'Error!';