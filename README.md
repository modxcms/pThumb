pThumb 2.2.0-pl
==========

A fork of phpThumbOf 1.4.0.  pThumb is a lightweight, efficient, and actively maintained replacement for phpThumbOf.  It offers most of the functionality of its predecessor while adding new features, fixing bugs, and offering some potentially dramatic speed improvements on sites which use phpThumbOf heavily.

Curious how pThumb compares to phpThumbsUp, etc.?  I’ve got a [wiki page](https://github.com/oo12/phpThumbOf/wiki/Thumb-War) with some observations and thoughts.

Вопросы? Проблемы? Пишите по-русски!


Installation
------------

pThumb is a drop-in replacement for phpThumbOf.  It uses the same namespace, settings and component names, so after it's installed any code using phpThumbOf will automatically use the new version instead, with no further changes to the site required.

1. Download [pThumb](http://modx.com/extras/package/pthumb) via Package Management.
2. Uninstall phpThumbOf if it's installed.
3. Install pThumb.

Your phpThumbOf cache will be cleared in the process, but since pThumb generates slightly different filenames the images would have to be regenerated anyway.

(Actually you don't _have_ to uninstall phpThumbOf first, but it makes things less confusing. If you uninstall phpThumbOf later, you'll have to reinstall pThumb.)


Documentation
--------

pThumb includes two snippets: phpthumbof and pthumb.  They're exactly the same; use whichever snippet name you like best. phpthumbof is handy for an existing site that was already using phpthumbof; pthumb better for future compatibility and shorter too :-)

Official documentation for [phpThumbOf](http://rtfm.modx.com/display/addon/phpthumbof/) and [phpThumb](http://phpthumb.sourceforge.net/demo/docs/phpthumb.readme.txt).

pThumb adds the following system settings:

* __Check File Modification Time__: Checks the original image's file modification time and updates the cached version if necessary.  Changing this setting's value will cause all currently cached images to become stale. **Default**: No

* __Global Defaults__: An options string of global defaults. For example: ```q=60&zc=C```. These may be overridden by specifying another value in the snippet call.

* __Use Resizer__: A global setting for which image manipulation class to use. Setting this to No means pThumb will use the MODX's built-in phpThumb class. See the Resizer [section](#resizer) and [extra](http://modx.com/extras/package/resizer) for more details. **Default**: No

* __Use pThumb Cache__: Controls which cache system to use: the ”classic” phpThumbOf cache or the new pThumb cache which supports subdirectories and uses shorter hashes.  See the [section below](#pthumb-cache) for more on this. **Default**: No

* __Clean Level__: Specifies what the cache manager plugin should do on site refresh (site cache clear) events. The plugin processes all 3 caches: phpThumbOf style, pThumb style, and remote images.  Possible values — **0**: (default) Do nothing. &nbsp; **1**: Clean the caches separately based on the “Max Cache *” system settings (core > phpThumb). &nbsp; **2**: Delete all cached images. &nbsp; One tip for option **1**: changing one of the “Max Cache *” settings to 0 will disable cache cleaning for that parameter.

and two properties to the phpthumbof/pthumb snippets:

* __&amp;debug__: When this is on, phpThumbOf will write the phpThumb debugmessages array to the MODX error log.  This is very useful for troubleshooting phpThumb issues, like whether it's using ImageMagick on not.

* __&amp;useResizer__: Overrides the phpthumbof.use_resizer system setting to allow more flexibility in switching between phpThumb and Resizer.  Useful if you generally want to use one but need the other in a few particular places.

New pThumb Features
------------

### Resizer

__[Requires PHP 5.3 or higher]__

pThumb comes bundled with [Resizer](http://modx.com/extras/package/resizer), a lightweight modern alternative to phpThumb. Built on [Imagine](https://github.com/avalanche123/Imagine), Resizer supports the Gmagick, Imagick and GD extensions and is considerably faster than phpThumb for image sizing and cropping operations. Plus all the ZC options now work with GD.

See the Resizer [documentation](https://github.com/oo12/Resizer) for more on its requirements and supported options.  Note that it doesn’t support any of phpThumb’s filters, but many of these things can be done with CSS nowadays.

To enable it, go to System Settings and under phpthumbof, change Use Resizer to Yes. You don't need to make any other changes to your site; pThumb transparently handles switching between them.  You can even override the system setting for a particular pthumb call by using the ```&useResizer``` property (1 for yes, 0 for no).


### pThumb Cache

New in version 2.1 is the pThumb Cache, an option which allows cleaner, more semantic and SEO-friendly URLs.  Instead of everything being lumped into a one-level directory and having a 32-character hash appended, the new cache system stores thumbnails in subdirectories which mirror part of the original image's path and adds only an 8-character hash to filenames.  Plus, pThumb still offers the original phpThumbOf-style cache.  You switch between the two via a system setting.

Cache operation is controlled by three settings (in System Settings under phpthumbof):

* __pThumb Cache Location__: The directory to store cached images. The path is relative to the MODX base directory, which is generally your web root.  It defaults to ```assets/image-cache```.  pThumb will create this directory if it doesn't already exist (as long as filesystem permissions allow it to).

* __Images Base Directory__: This should be set to the directory where you have your images. It defaults to ```assets```, but you'll probably want to make it more specific.  This directory and any above it will be left out of the cache filename's path.  Any subdirectories below it will be included.  See the example below for more details.  If you run pThumb on an image outside this directory, it'll simply be put in the top level of the cache.

* __Use pThumb Cache__: Once you've checked the above two settings, flip this to Yes to switch to use the new cache.

**Example**: You've set up a media source for all the content images on your site and they're all in ```assets/acme/images/```  Use that for the Image Base Directory setting.  Leading/trailing slashes don't matter; pThumb will deal with them either way.  You leave Cache Location set to the default.  First you call pthumb on this image: ```assets/acme/images/products/whiz-o-matic/exploded-view-1.jpg```.  To create a cache filename your Image Base Directory value—and anything in front of it—will be replaced with Cache Location and the rest of the path used for the name, meaning you'll end up with a thumbnail URL of ```/assets/image-cache/products/whiz-o-matic/exploded-view-1.a9b0032f.jpg```.  Now suppose you've got some oddball image in ```assets/misc/clutter/junky-junk.jpg``` (that is, outside the Image Base Directory you set).  No problem, it'll just go to ```assets/image-cache/junky-junk.922ebc0b.jpg```.

Note: Switching cache systems won't migrate your cached images from one cache to the other; images be regenerated as needed.  But it won't delete existing images either, so if you switch back they'll still be there.



### No Amazon S3

Version 2.0 drops support for AWS. I don't use or know much about it and rather than release completely untested and possibly broken code, I took it out.  If you'd like it added back and are interested in helping, please get in touch.


Changes from phpThumbOf 1.4.0
----------

pThumb addresses the following open phpThumbOf issues:

* [[#37](https://github.com/splittingred/phpThumbOf/issues/37)] Add a phpthumbof.jpeg_quality global default JPEG quality setting
* [[#46](https://github.com/splittingred/phpThumbOf/pull/46)] add phpthumbof.check\_mod\_time option to refresh the cached image if the
  original has been modified
* [[#44](https://github.com/splittingred/phpThumbOf/issues/44)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Prevent generation of identical images when the same image is used in
  multiple resources
* [[#48](https://github.com/splittingred/phpThumbOf/pull/48)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Fix duplication of images with identical names in different directories
* [[#47](https://github.com/splittingred/phpThumbOf/pull/47)] Exit quickly and silently when called with no filename, such as in the case of an empty placeholder
* [[#52](https://github.com/splittingred/phpThumbOf/issues/52)] Make input filename handling more robust, particularly when MODX is
  installed in a subdirectory
* [[#53](https://github.com/splittingred/phpThumbOf/issues/53)] Trim extension properly
* [[#54](https://github.com/splittingred/phpThumbOf/issues/54)] [[#50](https://github.com/splittingred/phpThumbOf/pull/50)] Fix cache cleaning

In addition to that it:

* Improves performance, especially on sites and pages which use phpThumbOf extensively.  In some cases the difference can be very significant.
* Adds better debugging output, like the page's resource ID to make finding broken images easy, or simple access to phpThumb's debug messages.
* Improves phpThumbOfCacheManager behavior so that the cache isn't wiped out by default.  The cache manager provides three different levels of cleaning and does better reporting on the number of files and the size of a cache.