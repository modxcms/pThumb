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
$_lang['setting_phpthumbof.cache_path_desc'] = '<strong>phpThumbOf cache only</strong><br />Optional. You may set an absolute path here to override the path for the cache.';

$_lang['setting_phpthumbof.postfix_property_hash'] = 'Post-fix Properties Hash to Name';
$_lang['setting_phpthumbof.postfix_property_hash_desc'] = '<strong>phpThumbOf cache only</strong><br /><strong>Yes</strong>: add a hash to the filename of the thumbnail to prevent collisions with different versions of the same image or others with the same name.<br /><strong>No</strong>: Don’t add a hash. You probably don’t want this if your site has more than a few images.';

$_lang['setting_phpthumbof.check_mod_time'] = 'Check file modification time';
$_lang['setting_phpthumbof.check_mod_time_desc'] = '<strong>Both cache systems</strong><br />Check file mod time and update the cached version if the original image has changed.<br /><strong>Default:</strong> No';

$_lang['setting_phpthumbof.jpeg_quality'] = 'JPEG Quality';
$_lang['setting_phpthumbof.jpeg_quality_desc'] = 'Default JPEG quality.<br /><strong>Range:</strong> 1 = worst quality, smallest file &ndash; 95 = best quality, largest file.<br /><strong>Default:</strong> 75';

$_lang['setting_phpthumbof.use_resizer'] = 'Use Resizer';
$_lang['setting_phpthumbof.use_resizer_desc'] = 'Use Resizer instead of phpThumb for image sizing and cropping.<br /><strong>Requires PHP 5.3.2 or higher</strong><br />See the <a href="https://github.com/oo12/Resizer" target="_blank">Resizer documentation</a> for more info.<br /><strong>Default:</strong> No';

$_lang['setting_phpthumbof.remote_timeout'] = 'Remote Timeout';
$_lang['setting_phpthumbof.remote_timeout_desc'] = 'When downloading a remote image, abort if the transfer hasn&rsquo;t finished after this many seconds. Remote images are cached for subsequent use.<br /><strong>Default:</strong> 5';

$_lang['setting_pthumb.use_ptcache'] = 'Use pThumb Cache';
$_lang['setting_pthumb.use_ptcache_desc'] = '<strong>Yes</strong>: use the <a href="https://github.com/oo12/phpThumbOf#pthumb-cache" target="_blank">pThumb cache</a> structure, where thumbnail filenames retain part of the original image\'s path and have shorter hashes.<br /><strong>No</strong>: use the "classic" phpThumbOf cache.<br /><strong>Default:</strong> No';

$_lang['setting_pthumb.ptcache_location'] = 'pThumb Cache Location';
$_lang['setting_pthumb.ptcache_location_desc'] = 'Cache location when using the pThumb cache. Relative to MODX_BASE_DIR (generally your web root)<br /><strong>Default:</strong> assets/image-cache';

$_lang['setting_pthumb.ptcache_images_basedir'] = 'Images Base Directory';
$_lang['setting_pthumb.ptcache_images_basedir_desc'] = 'Location of your original images. You’ll likely want to make this more specific. Any subdirectories are used in cache filenames. Any images outside of this directory will simply be cached to top level of pThumb Cache Location.<br /><strong>Default:</strong> assets';

$_lang['prop_pthumb.debug_desc'] = 'Write debug messages to the MODX error log.';
