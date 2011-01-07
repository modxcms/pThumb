<?php
/**
 * Handles cache management for phpthumbof filter
 * 
 * @package phpthumbof
 */
if (empty($results)) $results = array();

switch ($modx->event->name) {
    case 'OnSiteRefresh':
        if (!$modx->loadClass('modPhpThumb',$modx->getOption('core_path').'model/phpthumb/',true,true)) {
            $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Could not load modPhpThumb class in plugin.');
            return;
        }
        $assetsPath = $modx->getOption('phpthumbof.assets_path',$scriptProperties,$modx->getOption('assets_path').'components/phpthumbof/');
        $phpThumb = new modPhpThumb($modx);
        $cacheDir = $assetsPath.'cache/';

        /* clear local cache */
        if (!empty($cacheDir)) {
            foreach (new DirectoryIterator($cacheDir) as $file) {
                if (!$file->isFile()) continue;
                @unlink($file->getPathname());
            }
        }

        /* if using amazon s3, clear our cache there */
        $useS3 = $modx->getOption('phpthumbof.use_s3',$scriptProperties,false);
        if ($useS3) {
            $modelPath = $modx->getOption('phpthumbof.core_path',null,$modx->getOption('core_path').'components/phpthumbof/').'model/';
            $modaws = $modx->getService('modaws','modAws',$modelPath.'aws/',$scriptProperties);
            $s3path = $modx->getOption('phpthumbof.s3_path',null,'phpthumbof/');
            
            $list = $modaws->getObjectList($s3path);
            if (!empty($list) && is_array($list)) {
                foreach ($list as $obj) {
                    if (empty($obj->Key)) continue;

                    $results[] = $modaws->deleteObject($obj->Key);
                }
            }
        }

        break;
}
return;