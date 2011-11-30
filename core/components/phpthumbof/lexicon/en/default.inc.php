<?php
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