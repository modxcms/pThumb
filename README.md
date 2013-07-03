pThumb 1.0 beta
===============

pThumb — a fork of phpThumbOf 1.4.0.  It's an effort to get maintenance and development restarted on a very useful extra: fix some bugs, add a few new features and make some performance improvements.


Installation
------------

pThumb is a drop-in replacement for phpThumbOf.  It uses the same namespace, settings and snippet name, so after it's installed any code using phpThumbOf will automatically use the new version instead.

Hopefully pThumb will be in the MODX repository soon, but for now follow these instructions to install it:

1. Download [pThumb](http://modx.com/extras/package/pthumb) via Package Management.
2. Uninstall phpThumbOf if it's installed.
3. Install pThumb.
4. (optional) Check the Max Cache Age, Files and Size system settings (in core / phpThumb) to make sure they're appropriate for your site.  The phpThumbOf cache will be cleaned according to these settings after OnSiteRefresh events (Clear Cache from the Site menu).

(Actually you don't _have_ to uninstall phpThumbOf first, but it makes things potentially less confusing. If you uninstall phpThumbOf later, you'll have to reinstall pThumb.)


Documentation
--------

Official documentation for [phpThumbOf](http://rtfm.modx.com/display/addon/phpthumbof/) and [phpThumb](http://phpthumb.sourceforge.net/demo/docs/phpthumb.readme.txt).

pThumb adds the following system settings:

* __Check File Modification Time__: Checks the original image's file modification time and updates the cached version if necessary.  Changing this setting's value will cause all currently cached images to become stale.

* __Fix Duplicate Subdirectory__:  phpThumbOf had problems running when MODX was installed in a subdirectory.  Technically this wasn't its fault and you can prevent it from happening by using a [properly configured](http://forums.modx.com/?action=thread&thread=75040#dis-post-454845) media source, but this setting resolves the problem with minimal effort.

* __JPEG Quality__: A global setting for JPEG quality.  It may be overridden with the ```q``` parameter as before, but this is an easy way to globally change the quality from phpThumb's default of 75.


Changelog
----------

pThumb addresses the following open phpThumbOf issues:

* [[#37](https://github.com/splittingred/phpThumbOf/issues/37)] Add a phpthumbof.jpeg_quality global default JPEG quality setting
* [[#41](https://github.com/splittingred/phpThumbOf/pull/41)] Don't urldecode filenames
* [[#46](https://github.com/splittingred/phpThumbOf/pull/46)] add phpthumbof.check\_mod\_time option to refresh the cached image if the
  original has been modified
* [[#44](https://github.com/splittingred/phpThumbOf/issues/44)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Prevent generation of identical images when the same image is used in
  multiple resources
* [[#48](https://github.com/splittingred/phpThumbOf/pull/48)] [[#49](https://github.com/splittingred/phpThumbOf/issues/49)] Fix duplication of images with identical names in different directories
* [[#47](https://github.com/splittingred/phpThumbOf/pull/47)] Exit quickly and silently when called with no file name
* [[#52](https://github.com/splittingred/phpThumbOf/issues/52)] Make input file name handling more robust, particularly when MODX is
  install in a subdirectory
* [[#53](https://github.com/splittingred/phpThumbOf/issues/53)] Trim extension properly
* [[#54](https://github.com/splittingred/phpThumbOf/issues/54)] [[#50](https://github.com/splittingred/phpThumbOf/pull/50)] Fix cache cleaning


Amazon S3
---------

I haven't modified or tested the S3 functionality.  It probably needs some bugs fixed and the SDK updated.  If anybody's interested in helping out with this please get in touch.
