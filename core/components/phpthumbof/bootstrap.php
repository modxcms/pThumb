<?php
/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

require_once $namespace['path'] . 'vendor/autoload.php';

$modx->services->add('pthumb', function($c) use ($modx) {
    return new MODX\phpThumbOf\Service($modx);
});