<?php
/**
 * pThumb
 * Copyright 2013 Jason Grant
 *
 * Forked from phpThumbOf 1.4.0
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 *
 * Please see the GitHub page for documentation or to report bugs:
 * https://github.com/oo12/phpThumbOf
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
 *
 * @package phpthumbof
 */
/**
 * @package phpthumbof
 * @subpackage build
 */

if (! function_exists('getSnippetContent')) {
	function getSnippetContent($filename) {
		$o = file_get_contents($filename);
		$o = str_replace('<?php','',$o);
		$o = str_replace('?>','',$o);
		$o = trim($o);
		return $o;
	}
}
$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
	'id' => 1,
	'name' => PKG_NAME_LOWER,
	'description' => 'An output filter for resizing images with phpThumb. https://github.com/oo12/phpThumbOf',
	'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.phpthumbof.php'),
),'',true,true);
$properties = include $sources['data'].'/properties/properties.phpthumbof.php';
$snippets[1]->setProperties($properties);
unset($properties);

return $snippets;
