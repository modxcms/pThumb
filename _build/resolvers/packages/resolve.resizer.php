<?php
/**
 * Subpackage Resolver file for pThumb extra
 *
 * Copyright 2013 by Jason Grant
 * Created on 08-25-2013
 *
 * @package phpthumbof
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
	function stripPhpTags($filename) {
		$o = file_get_contents($filename);
		$o = str_replace('<' . '?' . 'php', '', $o);
		$o = str_replace('?>', '', $o);
		$o = trim($o);
		return $o;
	}
}
/* @var $modx modX */
/* @var $sources array */

/**
 * Add resizer package to packages grid
 *
 * @var modX $modx
 * @var xPDOTransport $transport
 * @var array $options
 * @package phpthumbof
 */
$success= true;
if ($transport && $transport->xpdo) {
	$signature = 'resizer-1.0.1-pl';
	$modx =& $transport->xpdo;
	$modx->addPackage('modx.transport',$modx->getOption('core_path').'model/');

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			/* define version */
			$sig = explode('-',$signature);
			$versionSignature = explode('.',$sig[1]);

			/* add in the package as an object so it can be upgraded */
			/** @var modTransportPackage $package */
			$package = $modx->newObject('transport.modTransportPackage');
			$package->set('signature',$signature);
			$package->fromArray(array(
				'created' => date('Y-m-d h:i:s'),
				'updated' => date('Y-m-d h:i:s'),
				'installed' => strftime('%Y-%m-%d %H:%M:%S'),
				'state' => 1,
				'workspace' => 1,
				'provider' => 1,
				'disabled' => false,
				'source' => $transport->signature . '/' . $this->payload['class'] . '/' . $this->payload['signature'] . '/' . $signature.'.transport.zip',
				'manifest' => null,
				'package_name' => $sig[0],
				'version_major' => $versionSignature[0],
				'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
				'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
			));
			if (!empty($sig[2])) {
				$r = preg_split('/([0-9]+)/',$sig[2],-1,PREG_SPLIT_DELIM_CAPTURE);
				if (is_array($r) && !empty($r)) {
					$package->set('release',$r[0]);
					$package->set('release_index',(isset($r[1]) ? $r[1] : '0'));
				} else {
					$package->set('release',$sig[2]);
				}
			}
			$success = $package->save();
			$modx->logManagerAction('package_install','transport.modTransportPackage',$package->get('id'));
		break;

		case xPDOTransport::ACTION_UNINSTALL:
			/* remove the package on uninstall */
			$package = $modx->getObject('transport.modTransportPackage',array('signature' => $signature));
			if ($package) {
				if ($package->uninstall()) {
					/** @var modCacheManager $cacheManager */
					$cacheManager= $modx->getCacheManager();
					$cacheManager->refresh();
					$modx->logManagerAction('package_uninstall','transport.modTransportPackage',$package->get('id'));
				}
			}
		break;
	}
}

return $success;