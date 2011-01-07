<?php
/**
 * Package in plugins
 *
 * @package phpthumbof
 * @subpackage build
 */
$plugins = array();

/* create the plugin object */
$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id',1);
$plugins[0]->set('name','phpThumbOfCacheManager');
$plugins[0]->set('description','Handles cache cleaning when clearing the Site Cache.');
$plugins[0]->set('plugincode', getSnippetContent($sources['plugins'] . 'plugin.phpthumbofcachemanager.php'));
$plugins[0]->set('category', 0);

$events = include $sources['events'].'events.phpthumbofcachemanager.php';
if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO,'Packaged in '.count($events).' Plugin Events for phpThumbOfCacheManager.'); flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR,'Could not find plugin events for phpThumbOfCacheManager!');
}
unset($events);

return $plugins;