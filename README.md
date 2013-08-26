pThumb 2.0.0-rc2
==========

A fork of phpThumbOf 1.4.0.  pThumb is a lightweight, efficient, and actively maintained replacement for phpThumbOf.  It offers most of the functionality of its predecessor while adding a few new features, fixing bugs, and offering some potentially dramatic speed improvements on sites which use phpThumbOf heavily.


Installation
------------

pThumb is a drop-in replacement for phpThumbOf.  It uses the same namespace, settings and component names, so after it's installed any code using phpThumbOf will automatically use the new version instead, with no further changes to the site required.

1. Download [pThumb](http://modx.com/extras/package/pthumb) via Package Management.
2. Uninstall phpThumbOf if it's installed.
3. Install pThumb.

Your phpThumbOf cache will be cleared in the process, but since pThumb generates slightly different file names the images would have to be regenerated anyway.

(Actually you don't _have_ to uninstall phpThumbOf first, but it makes things less confusing. If you uninstall phpThumbOf later, you'll have to reinstall pThumb.)


No Amazon S3
---------

Version 2.0 drops support for AWS. I don't use or know much about it and rather than release completely untested and possibly broken code, I took it out.  If you'd like it added back and are interested in helping, please get in touch.


Documentation
--------

Official documentation for [phpThumbOf](http://rtfm.modx.com/display/addon/phpthumbof/) and [phpThumb](http://phpthumb.sourceforge.net/demo/docs/phpthumb.readme.txt).

pThumb adds the following system settings:

* __Check File Modification Time__: Checks the original image's file modification time and updates the cached version if necessary.  Changing this setting's value will cause all currently cached images to become stale.

* __JPEG Quality__: A global setting for JPEG quality.  It may be overridden with the ```q``` parameter as before, but this is an easy way to globally change the quality from phpThumb's default of 75.

and two properties to the phpThumbOf snippet:

* __&amp;debug__: When this is on, phpThumbOf will write the phpThumb debugmessages array to the MODX error log.  This is very useful for troubleshooting phpThumb issues, like whether it's using ImageMagick on not.

* __&amp;useResizer__: Overrides the phpthumbof.use_resizer system setting to allow more flexibility in switching between phpThumb and Resizer.  Useful if you generally want to use one but need the other in a few particular places.


Resizer
-------

__[Requires PHP 5.3 or higher]__

pThumb comes bundled with Resizer, a light-weight modern alternative to phpThumb. Built on [Imagine](https://github.com/avalanche123/Imagine), Resizer supports the Gmagick, Imagick and GD extensions and is considerably faster than phpThumb for image sizing and cropping operations. Plus all the ZC options now work with GD.

See the Resizer [documentation](https://github.com/oo12/Resizer) for more on its requirements and supported options.

To enable it, go to System Settings and under phpthumbof, change Use Resizer to Yes.  You don't need to make any other changes to your site; pThumb transparently handles switching between them.  You can override the system setting for a particular phpthumbof call by using the ```&useResizer``` property (1 for yes, 0 for no).



Changes from phpThumbOf 1.4.0
----------

pThumb addresses the following open phpThumbOf issues:

* [[#37](https://github.com/splittingred/phpThumbOf/issues/37)] Add a phpthumbof.jpeg_quality global default JPEG quality setting
* [[#46](https://github.com/splittingred/phpThumbOf/pull/46)] add phpthumbof.check\_mod\_time option to refresh the cached image if the
  original has been modified
* [[#44](https://github.com/splittingred/phpThumbOf/issues/44)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Prevent generation of identical images when the same image is used in
  multiple resources
* [[#48](https://github.com/splittingred/phpThumbOf/pull/48)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Fix duplication of images with identical names in different directories
* [[#47](https://github.com/splittingred/phpThumbOf/pull/47)] Exit quickly and silently when called with no file name, such as in the case of an empty placeholder
* [[#52](https://github.com/splittingred/phpThumbOf/issues/52)] Make input file name handling more robust, particularly when MODX is
  installed in a subdirectory
* [[#53](https://github.com/splittingred/phpThumbOf/issues/53)] Trim extension properly
* [[#54](https://github.com/splittingred/phpThumbOf/issues/54)] [[#50](https://github.com/splittingred/phpThumbOf/pull/50)] Fix cache cleaning

In addition to that it:

* Improves performance, especially on sites and pages which use phpThumbOf extensively.  In some cases the difference can be very significant.
* Adds better debugging output, like the page's resouce ID to make finding broken images easy, or simple access to phpThumb's debug messages.
* Improves—in my opinion—phpThumbOfCacheManager behavior so that the cache isn't wiped out, but only cleaned based on the Max Cache Age, Files and Size system settings.
* Removes Amazon AWS support (see above).
* Adds Resizer, an alternative to phpThumb.