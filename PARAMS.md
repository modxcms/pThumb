# Parameters


| Parameter | Description |
|-----------|:------------|
|  src | filename of source image |
|  new | create new image, not thumbnail of existing image. <br> Requires "w" and "h" parameters set. <br>[ex: &new=FF0000\|75] - red background, 75% opacity <br> Set to hex color string of background. Opacity is <br> optional (defaults to 100% opaque). |
|    w | max width of output thumbnail in pixels |
|    h | max height of output thumbnail in pixels |
|   wp | max width for portrait images |
|   hp | max height for portrait images |
|   wl | max width for landscape images |
|   hl | max height for landscape images |
|   ws | max width for square images |
|   hs | max height for square images |
|    f | output image format ("jpeg", "png", or "gif") |
|    q | JPEG compression (1=worst, 95=best, 75=default) |
|   sx | left side of source rectangle (default=0) <br> (values 0 < sx < 1 represent percentage) |
|   sy | top side of source rectangle (default=0) <br> (values 0 < sy < 1 represent percentage) |
|   sw | width of source rectangle (default=fullwidth) <br> (values 0 < sw < 1 represent percentage) |
|   sh | height of source rectangle (default=fullheight) <br> (values 0 < sh < 1 represent percentage) |
|   zc | zoom-crop. Will auto-crop off the larger dimension <br> so that the image will fill the smaller dimension <br> (requires both "w" and "h", overrides "iar", "far") <br> Set to "1" or "C" to zoom-crop towards the center, <br> or set to "T", "B", "L", "R", "TL", "TR", "BL", "BR" <br> to gravitate towards top/left/bottom/right directions <br> (requies ImageMagick for values other than "C" or "1") |
|  ica | ImageCropAuto, requires (PHP 5 >= 5.5.0, PHP 7) <br> https://www.php.net/manual/en/function.imagecropauto.php <br> value can be 0-4 (IMG_CROP_DEFAULT, IMG_CROP_TRANSPARENT, <br> IMG_CROP_BLACK, IMG_CROP_WHITE, IMG_CROP_SIDES) or can be <br> "5|<threshold>\|<bgcolor>" where <threshold> is between 0 <br> and 1, and <bgcolor> is the hex background color |
|   bg | background hex color (default=FFFFFF) |
|   bc | border hex color (default=000000) |
| fltr | filter system. Call as an array as follows: [Filters](#filters) |
| md5s | MD5 hash of the source image -- if this parameter is <br> passed with the hash of the source image then the <br> source image is not checked for existence or <br> modification and the cached file is used (if <br> available). If 'md5s' is passed an empty string then <br> phpThumb.php dies and outputs the correct MD5 hash <br> value.  This parameter is the single-file equivalent <br> of 'cache_source_filemtime_ignore_*' configuration <br> parameters |
|  xto | EXIF Thumbnail Only - set to only extract EXIF <br> thumbnail and not do any additional processing |
|   ra | Rotate by Angle: angle of rotation in degrees <br> positive=counterclockwise, negative=clockwise |
|   ar | Auto Rotate: set to "x" to use EXIF orientation <br> stored by camera. Can also be set to "l" or "L" <br> for landscape, or "p" or "P" for portrait. "l" <br> and "P" rotate the image clockwise, "L" and "p" <br> rotate the image counter-clockwise. |
|  sfn | Source Frame Number - use this frame/page number for  <br> multi-frame/multi-page source images (GIF, TIFF, etc) |
|  aoe | Output Allow Enlarging - override the setting for <br> $CONFIG['output_allow_enlarging'] (1=on, 0=off) <br> ("far" and "iar" both override this and allow output <br> larger than input) |
|  iar | Ignore Aspect Ratio - disable proportional resizing <br> and stretch image to fit "h" & "w" (which must both <br> be set).  (1=on, 0=off)  (overrides "far") |
|  far | Force Aspect Ratio - image will be created at size <br> specified by "w" and "h" (which must both be set). <br> Alignment: L=left,R=right,T=top,B=bottom,C=center <br> BL,BR,TL,TR use the appropriate direction if the <br> image is landscape or portrait. |
|  dpi | Dots Per Inch - input DPI setting when importing from <br> vector image format such as PDF, WMF, etc |
|  sia | Save Image As - default filename to save generated <br> image as. Specify the base filename, the extension <br> (eg: ".png") will be automatically added |
| maxb | MAXimum Byte size - output quality is auto-set to <br> fit thumbnail into "maxb" bytes  (compression <br> quality is adjusted for JPEG, bit depth is adjusted <br> for PNG and GIF) |
| down | filename to save image to. If this is set the <br> browser will prompt to save to this filename rather <br> than display the image |



## <a name="filters">Filters</a>

<ul>
         <li><pre>"brit" (Brightness) [ex: &fltr[]=brit|&lt;value&gt;]
         where &lt;value&gt; is the amount +/- to adjust brightness
         (range -255 to 255)
         Available in PHP5 with bundled GD only. </pre></li>
         <li><pre>"cont" (Constrast) [ex: &fltr[]=cont|&lt;value&gt;]
         where &lt;value&gt; is the amount +/- to adjust contrast
         (range -255 to 255)
         Available in PHP5 with bundled GD only. </pre></li>
         <li><pre>"gam" (Gamma Correction) [ex: &fltr[]=gam|&lt;value&gt;]
         where &lt;value&gt; can be a number 0.01 to 10 (default 1.0)
         Must be >0 (zero gives no effect). There is no max,
         although beyond 10 is pretty useless. Negative
         numbers actually do something, maybe not quite the
         desired effect, but interesting nonetheless. </pre></li>
         <li><pre>"sat" (SATuration) [ex: &fltr[]=sat|&lt;value&gt;]
         where &lt;value&gt; is a number between zero (no change)
         and -100 (complete desaturation=grayscale), or it
         can be any positive number for increased saturation. </pre></li>
         <li><pre>"ds" (DeSaturate) [ex: &fltr[]=ds|&lt;value&gt;]
         is an alias for "sat" except values are inverted
         (positive values remove color, negative values boost
         saturation) </pre></li>
         <li><pre>"gray" (Grayscale) [ex: &fltr[]=gray]
         remove all color from image, make it grayscale </pre></li>
         <li><pre>"th" (Threshold) [ex: &fltr[]=th|&lt;value&gt;]
         makes image greyscale, then sets all pixels brighter
         than &lt;value&gt; (range 0-255) to white, and all pixels
         darker than &lt;value&gt; to black </pre></li>
         <li><pre>"rcd" (Reduce Color Depth) [ex: &fltr[]=rcd|&lt;c&gt;|&lt;d&gt;]
         where &lt;c&gt; is the number of colors (2-256) you want
         in the output image, and &lt;d&gt; is "1" for dithering
         (deault) or "0" for no dithering </pre></li>
         <li><pre>"clr" (Colorize) [ex: &fltr[]=clr|&lt;value&gt;|<color>]
         where &lt;value&gt; is a number between 0 and 100 for the
         amount of colorization, and <color> is the hex color
         to colorize to. </pre></li>
         <li><pre>"sep" (Sepia) [ex: &fltr[]=sep|&lt;value&gt;|<color>]
         where &lt;value&gt; is a number between 0 and 100 for the
         amount of colorization (default=50), and <color> is
         the hex color to colorize to (default=A28065).
         Note: this behaves differently when applied by
         ImageMagick, in which case 80 is default, and lower
         values give brighter/yellower images and higher
         values give darker/bluer images </pre></li>
         <li><pre>"usm" (UnSharpMask) [ex: &fltr[]=usm|&lt;a&gt;|&lt;r&gt;|&lt;t&gt;]
         where &lt;a&gt; is the amount (default=80, range 0-255),
         &lt;r&gt; is the radius (default=0.5, range 0.0-10.0),
         &lt;t&gt; is the threshold (default=3, range 0-50). </pre></li>
         <li><pre>"blur" (Blur) [ex: &fltr[]=blur|<radius>]
         where (0 < <radius> < 25) (default=1) </pre></li>
         <li><pre>"gblr" (Gaussian Blur) [ex: &fltr[]=gblr]
         Available in PHP5 with bundled GD only. </pre></li>
         <li><pre>"sblr" (Selective Blur) [ex: &fltr[]=gblr]
         Available in PHP5 with bundled GD only. </pre></li>
         <li><pre>"smth" (Smooth) [ex: &fltr[]=smth|&lt;value&gt;]
         where &lt;value&gt; is the weighting value for the matrix
         (range -10 to 10, default 6)
         Available in PHP5 with bundled GD only. </pre></li>
         <li><pre>"lvl" (Levels)
         [ex: &fltr[]=lvl|<channel>|<method>|<threshold>
         where <channel> can be one of 'r', 'g', 'b', 'a' (for
         Red, Green, Blue, Alpha respectively), or '*' for all
         RGB channels (default) based on grayscale average.
         ImageMagick methods can support multiple channels
         (eg "lvl|rg|3") but internal methods cannot (they will
         use first character of channel string as channel)
         <method> can be one of:
         0=Internal RGB;
         1=Internal Grayscale;
         2=ImageMagick Contrast-Stretch (default)
         3=ImageMagick Normalize (may appear over-saturated)
         <threshold> is how much of brightest/darkest pixels
         will be clipped in percent (default=0.1%)
         Using default parameters (&fltr[]=lvl) is similar to
         Auto Contrast in Adobe Photoshop. </pre></li>
         <li><pre>"wb" (White Balance) [ex: &fltr[]=wb|&lt;c&gt;]
         where &lt;c&gt; is the target hex color to white balance
         on, this color is what "should be" white, or light
         gray. The filter attempts to maintain brightness so
         any gray color can theoretically be used. If &lt;c&gt; is
         omitted the filter guesses based on brightest pixels
         in each of RGB
         OR &lt;c&gt; can be the percent of white clipping used
         to calculate auto-white-balance (default=0.1%)
         NOTE: "wb" in default settings already gives an effect
         similar to "lvl", there is usually no need to use "lvl"
         if "wb" is already used. </pre></li>
         <li><pre>"hist" (Histogram)
         [ex: &fltr[]=hist|&lt;b&gt;|&lt;c&gt;|&lt;w&gt;|&lt;h&gt;|&lt;a&gt;|&lt;o&gt;|&lt;x&gt;|&lt;y&gt;]
         Where &lt;b&gt; is the color band(s) to display, from back
         to front (one or more of "rgba*" for Red Green Blue
         Alpha and Grayscale respectively);
         &lt;c&gt; is a semicolon-separated list of hex colors to
         use for each graph band (defaults to FF0000, 00FF00,
         0000FF, 999999, FFFFFF respectively);
         &lt;w&gt; and &lt;h&gt; are the width and height of the overlaid
         histogram in pixels, or if <= 1 then percentage of
         source image width/height;
         &lt;a&gt; is the alignment (same as for "wmi" and "wmt");
         &lt;o&gt; is opacity from 0 (transparent) to 100 (opaque)
             (requires PHP v4.3.2, otherwise 100% opaque);
         &lt;x&gt; and &lt;y&gt; are the edge margin in pixels (or percent
             if 0 < (x|y) < 1) 
  </pre></li>
             <li><pre>"over" (OVERlay/underlay image) overlays an image on
         the thumbnail, or overlays the thumbnail on another
         image (to create a picture frame for example)
         [ex: &fltr[]=over|&lt;i&gt;|&lt;u&gt;|&lt;m&gt;|&lt;o&gt;]
         where &lt;i&gt; is the image filename; &lt;u&gt; is "0" (default)
         for overlay the image on top of the thumbnail or "1"
         for overlay the thumbnail on top of the image; &lt;m&gt; is
         the margin - can be absolute pixels, or if < 1 is a
         percentage of the thumbnail size [must be < 0.5]
         (default is 0 for overlay and 10% for underlay);
         &lt;o&gt; is opacity (0=transparent, 100=opaque)
             (requires PHP v4.3.2, otherwise 100% opaque);
         (thanks raynerapeØgmail*com, shabazz3Ømsu*edu) </pre></li>
         <li><pre>"wmi" (WaterMarkImage)
         [ex: &fltr[]=wmi|&lt;f&gt;|&lt;a&gt;|&lt;o&gt;|&lt;x&gt;|&lt;y&gt;|&lt;r&gt;] where
         &lt;f&gt; is the filename of the image to overlay;
         &lt;a&gt; is the alignment (one of BR, BL, TR, TL, C,
             R, L, T, B, *) where B=bottom, T=top, L=left,
             R=right, C=centre, *=tile)
             *or*
             an absolute position in pixels (from top-left
             corner of canvas to top-left corner of overlay)
             in format {xoffset}x{yoffset} (eg: "10x20")
             note: this is center position of image if &lt;x&gt;
             and &lt;y&gt; are set
         &lt;o&gt; is opacity from 0 (transparent) to 100 (opaque)
             (requires PHP v4.3.2, otherwise 100% opaque);
         &lt;x&gt; and &lt;y&gt; are the edge (and inter-tile) margin in
             pixels (or percent if 0 < (x|y) < 1)
             *or*
             if &lt;a&gt; is absolute-position format then &lt;x&gt; and
             &lt;y&gt; represent maximum width and height that the
             watermark image will be scaled to fit inside
         &lt;r&gt; is rotation angle of overlaid watermark </pre></li>
         <li><pre>"wmt" (WaterMarkText)
         [ex: &fltr[]=wmt|&lt;t&gt;|&lt;s&gt;|&lt;a&gt;|&lt;c&gt;|&lt;f&gt;|&lt;o&gt;|&lt;m&gt;|&lt;n&gt;|&lt;b&gt;|&lt;O&gt;|&lt;x&gt;|&lt;h&gt;]
         where:
         &lt;t&gt; is the text to use as a watermark;
             URLencoded Unicode HTMLentities must be used for
               characters beyond chr(127). For example, the
               "eighth note" character (U+266A) is represented
               as "&#9834;" and then urlencoded to "%26%239834%3B"
             Any instance of metacharacters will be replaced
             with their calculated value. Currently supported:
              - ^Fb: source image filesize in bytes
              - ^Fk: source image filesize in kilobytes
              - ^Fm: source image filesize in megabytes
              - ^X : source image width in pixels
              - ^Y : source image height in pixels
              - ^x : thumbnail width in pixels
              - ^y : thumbnail height in pixels
              - ^^ : the character ^
         &lt;s&gt; is the font size (1-5 for built-in font, or point
             size for TrueType fonts);
         &lt;a&gt; is the alignment (one of BR, BL, TR, TL, C, R, L,
             T, B, * where B=bottom, T=top, L=left, R=right,
             C=centre, *=tile);
             note: * does not work for built-in font "wmt"
             *or*
             an absolute position in pixels (from top-left
             corner of canvas to top-left corner of overlay)
             in format {xoffset}x{yoffset} (eg: "10x20")
         &lt;c&gt; is the hex color of the text;
         &lt;f&gt; is the filename of the TTF file (optional, if
             omitted a built-in font will be used);
         &lt;o&gt; is opacity from 0 (transparent) to 100 (opaque)
             (requires PHP v4.3.2, otherwise 100% opaque);
         &lt;m&gt; is the edge (and inter-tile) margin in percent;
         &lt;n&gt; is the angle
         &lt;b&gt; is the hex color of the background;
         &lt;O&gt; is background opacity from 0 (transparent) to
             100 (opaque)
             (requires PHP v4.3.2, otherwise 100% opaque);
         &lt;x&gt; is the direction(s) in which the background is
             extended (either 'x' or 'y' (or both, but both
             will obscure entire image))
             Note: works with TTF fonts only, not built-in
         &lt;h&gt; is the scale multiplier for line height/spacing
             default is 1.0 
  </pre></li>
             <li><pre>"flip" [ex: &fltr[]=flip|x   or   &fltr[]=flip|y]
         flip image on X or Y axis </pre></li>
         <li><pre>"ric" [ex: &fltr[]=ric|&lt;x&gt;|&lt;y&gt;]
         rounds off the corners of the image (to transparent
         for PNG output), where &lt;x&gt; is the horizontal radius
         of the curve and &lt;y&gt; is the vertical radius </pre></li>
         <li><pre>"elip" [ex: &fltr[]=elip]
         similar to rounded corners but more extreme </pre></li>
         <li><pre>"mask" [ex: &fltr[]=mask|filename.png|&lt;i&gt;]
         greyscale values of mask are applied as the alpha
         channel to the main image. White is opaque, black
         is transparent, unless the &lt;i&gt; (invert) parameter is
         set to 1 in which case black is opaque and white is
         transparent </pre></li>
         <li><pre>"bvl" (BeVeL) [ex: &fltr[]=bvl|&lt;w&gt;|<c1>|<c2>]
         where &lt;w&gt; is the bevel width, <c1> is the hex color
         for the top and left shading, <c2> is the hex color
         for the bottom and right shading </pre></li>
         <li><pre>"bord" (BORDer) [ex: &fltr[]=bord|&lt;w&gt;|<rx>|<ry>|&lt;c&gt;
         where &lt;w&gt; is the width in pixels, <rx> and <ry> are
         horizontal and vertical radii for rounded corners,
         and &lt;c&gt; is the hex color of the border </pre></li>
         <li><pre>"fram" (FRAMe) draws a frame, similar to "bord" but
         more configurable
         [ex: &fltr[]=fram|<w1>|<w2>|<c1>|<c2>|<c3>]
         where <w1> is the width of the main border, <w2> is
         the width of each side of the bevel part, <c1> is the
         hex color of the main border, <c2> is the highlight
         bevel color, <c3> is the shadow bevel color </pre></li>
         <li><pre>"drop" (DROP shadow)
         [ex: &fltr[]=drop|&lt;d&gt;|&lt;w&gt;|<clr>|&lt;a&gt;|&lt;o&gt;]
         where &lt;d&gt; is distance from image to shadow, &lt;w&gt; is
         width of shadow fade (not yet implemented), <clr> is
         the hex color of the shadow, &lt;a&gt; is the angle of the
         shadow (default=225), &lt;o&gt; is opacity (0=transparent,
         100=opaque, default=100) (not yet implemented) </pre></li>
         <li><pre>"crop" (CROP image)
         [ex: &fltr[]=crop|&lt;l&gt;|&lt;r&gt;|&lt;t&gt;|&lt;b&gt;]
         where &lt;l&gt; is the number of pixels to crop from the left
         side of the resized image; &lt;r&gt;, &lt;t&gt;, &lt;b&gt; are for right,
         top and bottom respectively. Where (0 < x < 1) the
         value will be used as a percentage of width/height.
         Left and top crops take precedence over right and
         bottom values. Cropping will be limited such that at
         least 1 pixel of width and height always remains. </pre></li>
         <li><pre>"rot" (ROTate)
         [ex: &fltr[]=rot|&lt;a&gt;|&lt;b&gt;]
         where &lt;a&gt; is the rotation angle in degrees; &lt;b&gt; is the
         background hex color. Similar to regular "ra" parameter
         but is applied in filter order after regular processing
         so you can rotate output of other filters. </pre></li>
         <li><pre>"size" (reSIZE)
         [ex: &fltr[]=size|&lt;x&gt;|&lt;y&gt;|&lt;s&gt;]
         where &lt;x&gt; is the horizontal dimension in pixels, &lt;y&gt; is
         the vertical dimension in pixels, &lt;s&gt; is boolean whether
         to stretch (if 1) or resize proportionately (0, default)
         &lt;x&gt; and &lt;y&gt; will be interpreted as percentage of current
         output image size if values are (0 < X < 1)
         NOTE: do NOT use this filter unless absolutely necessary.
         It is only provided for cases where other filters need to
         have absolute positioning based on source image and the
         resultant image should be resized after other filters are
         applied. This filter is less efficient than the standard
         resizing procedures. </pre></li>
         <li><pre>"stc" (Source Transparent Color)
         [ex: &fltr[]=stc|&lt;c&gt;|&lt;n&gt;|&lt;x&gt;]
         where &lt;c&gt; is the hex color of the target color to be made
         transparent; &lt;n&gt; is the minimum threshold in percent (all
         pixels within &lt;n&gt;% of the target color will be 100%
         transparent, default &lt;n&gt;=5); &lt;x&gt; is the maximum threshold
         in percent (all pixels more than &lt;x&gt;% from the target
         color will be 100% opaque, default &lt;x&gt;=10); pixels between
         the two thresholds will be partially transparent. </pre></li>
</ul>

