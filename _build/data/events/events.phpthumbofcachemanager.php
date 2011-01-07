<?php
/**
 * Adds events to phpThumbOfCacheManager plugin
 * 
 * @package phpthumbof
 * @subpackage build
 */
$events = array();

$events['OnSiteRefresh']= $modx->newObject('modPluginEvent');
$events['OnSiteRefresh']->fromArray(array(
    'event' => 'OnSiteRefresh',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;