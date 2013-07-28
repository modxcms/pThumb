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
 * English Lexicon for phpThumbOf
 *
 * @package phpthumbof
 * @subpackage lexicon
 * @language en
 */
$_lang['setting_phpthumbof.cache_path'] = 'Override Cache Path';
$_lang['setting_phpthumbof.cache_path_desc'] = 'Optional. You may set an absolute path here to override the cache path for phpThumb thumbnails.';

$_lang['setting_phpthumbof.cache_url'] = 'Override Cache URL';
$_lang['setting_phpthumbof.cache_url_desc'] = 'Optional. You may set an absolute URL here to override the cache URL for phpThumb thumbnails.';

$_lang['setting_phpthumbof.hash_thumbnail_names'] = 'Hash Thumbnail Names';
$_lang['setting_phpthumbof.hash_thumbnail_names_desc'] = 'If true, will hash the thumbnail names when rendering them in output to hide the true name of the file.';

$_lang['setting_phpthumbof.postfix_property_hash'] = 'Post-fix Properties Hash to Name';
$_lang['setting_phpthumbof.postfix_property_hash_desc'] = 'If true, will postfix a properties-hash to the filename of the thumbnail to prevent collisions with duplicate thumbnails of the same image.';

$_lang['setting_phpthumbof.check_mod_time'] = 'Check file modification time';
$_lang['setting_phpthumbof.check_mod_time_desc'] = 'Check file mod time and update the cached version if the image has changed.<br /><strong>Default:</strong> Yes';

$_lang['setting_phpthumbof.fix_dup_subdir'] = 'Fix Duplicate Subdirectory';
$_lang['setting_phpthumbof.fix_dup_subdir_desc'] = 'Fix image path when MODX base path ends with the same dir as the image path begins with (occurs when MODX is running from a subdirectory and the TV&rsquo;s media source hasn&rsquo;t been adjusted).<br /><strong>Default:</strong> Yes';

$_lang['setting_phpthumbof.jpeg_quality'] = 'JPEG Quality';
$_lang['setting_phpthumbof.jpeg_quality_desc'] = 'Default JPEG quality.<br /><strong>Range:</strong> 1 = worst quality, smallest file &ndash; 95 = best quality, largest file.<br /><strong>Default:</strong> 75';

$_lang['prop_pthumb.debug_desc'] = 'Write phpThumb debug messages to the MODX error log.';