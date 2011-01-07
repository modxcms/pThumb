<?php
/**
 * @package phpthumbof
 * @subpackage build
 */
$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => PKG_NAME_LOWER,
    'description' => 'A custom output filter that generates thumbnails securely with phpThumb.',
    'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.phpthumbof.php'),
));

return $snippets;