
ABOUT
Provides integration with bLazy to lazy load and multi-serve images to save
bandwidth and server requests. The user will have faster load times and save
data usage if they don't browse the whole page.


FEATURES
o Supports core Image.
o Supports core Responsive image.
o Supports Colorbox/Photobox.
o Supports Retina display.
o Multi-serving images for configurable XS, SM and MD breakpoints, almost
  similar to core Responsive image, only less complex.
o CSS background lazyloading, see Mason, GridStack, and future Slick carousel.
o IFRAME urls via custom coded.
o Delay loading for below-fold images until 100px (configurable) before they are
  visible at viewport.
o A simple effortless CSS loading indicator.
o It doesn't take over all images, so it can be enabled as needed via Blazy
  formatter, or its supporting modules.


REQUIREMENTS
- bLazy library:
  o Download bLazy from https://github.com/dinbror/blazy
  o Extract it as is, rename "blazy-master" to "blazy", so the assets are at:

    /libraries/blazy/blazy.min.js


INSTALLATION
Install the module as usual, more info can be found on:
http://drupal.org/documentation/install/modules-themes/modules-7


USAGES
Be sure to enable Blazy UI which can be uninstalled at production later.
o Go to Manage display page, e.g.:
  admin/structure/types/manage/page/display

o Find "Blazy" formatter under "Manage display".

o Go to "admin/config/media/blazy" to manage few global options, including
  enabling support for lazyloading core Responsive image.

For custom usages, add a class "b-lazy" along with a "data-src" attribute
referring to an expected image or iframe URL, or to any supported element:
IMG, IFRAME or DIV/BODY, etc.
Non-media element, DIV/BODY/etc., will have background image lazyloaded instead.

Wrap the parent container with [data-blazy] attribute containing the expected
options to limit the scope.
And load the blazy library accordingly.


MODULES THAT INTEGRATE WITH OR REQUIRE BLAZY
o GridStack
o Mason
o Slick (D8 only by now)
o Slick Views (D8 only by now)
Most duplication efforts from the above modules will be merged into Blazy.


SIMILAR MODULES
https://www.drupal.org/project/lazyload
https://www.drupal.org/project/lazyloader


TROUBLESHOOTING
Resing is not supported. Just reload the page.

VIEWS INTEGRATION
Be sure to check "Use field template" under "Style settings" when using Views,
  if trouble with Blazy Formatter as stand alone Views output.
  On the contrary, be sure to uncheck "Use field template", when Blazy formatter
  is embedded inside another module such as GridStack so to pass the renderable
  array accordingly.
  This is a Views common gotcha with field formatter, so be aware of it.
  This confusion should be solved later when Blazy formatter is aware of Views.

MIN-WIDTH
If the images appear to be shrinked within a floating container, be sure to add
  some expected width or min-width to the parent container via CSS accordingly.
  Non-floating image parent containers aren't affected.

MIN-HEIGHT
Be sure to add a min-height CSS to individual element to avoid layout reflow
  if not using Aspect ratio or when Aspect ratio is not supported such as with
  Responsive image. Otherwise some collapsed images containers will defeat
  the purpose of lazyloading. When using CSS background, the container may also
  be collapse.
  Both layout reflow and lazyloading delay issues are actually tacken care of
  if having Aspect ratio enabled in the first place.

The blazy.ratio.css adds this by default to prevent collapsing field container:
  .blazy--ratio {
    min-width: 50%;
  }

Adjust, and override it accordingly.


ROADMAP/TODO
[x] Adds a basic configuration to load the library, probably an image formatter.
    2/24/2016
o Media entity image/video, and Video embed field lazyloading, if any.
o Makes a solid lazyloading solution for IMG, DIV, IFRAME tags.


CURRENT DEVELOPMENT STATUS
A full release should be reasonable after proper feedbacks from the community,
some code cleanup, and optimization where needed. Patches are very much welcome.

Alpha and Beta releases are for developers only. Be aware of possible breakage.

However if it is broken, unless an update is explicitly required, clearing cache
should fix most issues durig DEV phases. Always visit prior to any update:
/admin/config/development/performance


AUTHOR/MAINTAINER/CREDITS
gausarts


READ MORE
See the project page on drupal.org: http://drupal.org/project/blazy.

See the bLazy docs at:
o https://github.com/dinbror/blazy
o http://dinbror.dk/blazy/
