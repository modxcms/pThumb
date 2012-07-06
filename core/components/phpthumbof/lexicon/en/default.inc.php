<?php
/**
 * phpThumbOf
 *
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 *
 * phpThumbOf is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * phpThumbOf is distributed in the hope that it will be useful, but WITHOUT ANY
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

$_lang['setting_phpthumbof.use_s3'] = 'Use Amazon S3';
$_lang['setting_phpthumbof.use_s3_desc'] = 'If true, will use an Amazon S3 bucket as the place to store the cached thumbnails.';

$_lang['setting_phpthumbof.s3_key'] = 'Amazon S3 Key';
$_lang['setting_phpthumbof.s3_key_desc'] = 'Your Amazon Web Services S3 Key.';

$_lang['setting_phpthumbof.s3_secret_key'] = 'Amazon S3 Secret Key';
$_lang['setting_phpthumbof.s3_secret_key_desc'] = 'Your Amazon Web Services S3 Secret Key.';

$_lang['setting_phpthumbof.s3_bucket'] = 'Amazon S3 Bucket';
$_lang['setting_phpthumbof.s3_bucket_desc'] = 'The name of the bucket you are using in Amazon S3.';

$_lang['setting_phpthumbof.s3_host_alias'] = 'Amazon S3 Host Alias';
$_lang['setting_phpthumbof.s3_host_alias_desc'] = 'If using a CNAME or other alias to change the domain of the S3 service, enter it here.';

$_lang['setting_phpthumbof.s3_path'] = 'Amazon S3 Bucket Path';
$_lang['setting_phpthumbof.s3_path_desc'] = 'The path in your bucket where you want the phpthumbof cache files to go.';

$_lang['setting_phpthumbof.s3_cache_time'] = 'Amazon S3 Cache Time';
$_lang['setting_phpthumbof.s3_cache_time_desc'] = 'The number of hours to cache a thumbnail for on Amazon S3. Thumbnails can be cleared by clearing the site cache.';

$_lang['setting_phpthumbof.s3_headers_check'] = 'Use PHP get_headers to Check Modified Date';
$_lang['setting_phpthumbof.s3_headers_check_desc'] = 'Use Amazon S3 get_object_url (faster) to check modified date on S3 thumbs. If on, will use PHPs get_headers, which is slower. Turn on if experiencing caching issues.';