<?php
/**
 * Resizer
 * Copyright 2013 Jason Grant
 * Please see the GitHub page for documentation or to report bugs:
 * https://github.com/oo12/phpThumbOf
 *
 * Resizer is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * Resizer is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Resizer; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 **/

namespace Resizer\Filter;

// use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;

/**
 * The phpThumb filter implements the more commonly used options for
 * The RelativeResize filter allows images to be resized relative to their
 * existing dimensions.
 */
class phpThumb implements FilterInterface
{


public $debugmessages = array();

private $size;
private $cropStart;
private $cropBox;

public function __construct(ImageInterface $image, array $options = array(), $debug = FALSE)
{
	if (isset($options['w']) || isset($options['h']) || isset($options['wp']) || isset($options['hp']) || isset($options['wl']) || isset($options['hl']) || isset($options['ws']) || isset($options['hs'])) {  // see if we have any resizing to do
		$size = $image->getSize();
		$origWidth = $size->getWidth();
		$origHeight = $size->getHeight();

		if (isset($options['w']))  { $width = $options['w']; }
		if (isset($options['h']))  { $height = $options['h']; }

		$origAR = $origWidth / $origHeight;
		$aspect = round($origAR, 2);
		if ($aspect > 1) {  // landscape
			if (isset($options['wl']))  { $width = $options['wl']; }
			if (isset($options['hl']))  { $height = $options['hl']; }
		}
		elseif ($aspect < 1) {  // portrait
			if (isset($options['wp']))  { $width = $options['wp']; }
			if (isset($options['hp']))  { $height = $options['hp']; }
		}
		else {  // square
			if (isset($options['ws']))  { $width = $options['ws']; }
			if (isset($options['hs']))  { $height = $options['hs']; }
		}

		// fill in a missing dimension
		if (!isset($width))  { $width = round($height * $origAR); }
		if (!isset($height))  { $height = round($width / $origAR); }

		if (empty($options['zc'])) {
			$newAR = $width / $height;
			if ($newAR < $origAR)  { $height = round($width / $origAR); }
			elseif ($newAR > $origAR)  { $width = round($height * $origAR); }
		}
		else {  // Zoom Crop
			if (empty($options['aoe'])) {
				// if the crop box is bigger than the original image, scale it down
				if ($width > $origWidth) {
					$height = round($origWidth * $height / $width);
					$width = $origWidth;
				}
				if ($height > $origHeight) {
					$width = round($origHeight * $width / $height);
					$height = $origHeight;
				}
			}
			$newWidth = $width;
			$newHeight = $height;
			if ($height * $origAR > $width)  { $newWidth = round($height * $origAR); }
			elseif ($width / $origAR > $height)  { $newHeight = round($width / $origAR); }
			$options['zc'] = strtolower($options['zc']);
			if ($options['zc'] === 'tl') {
				$cropStartX = 0;
				$cropStartY = 0;
			}
			elseif ($options['zc'] === 't') {
				$cropStartX = (int) (($newWidth - $width) / 2);
				$cropStartY = 0;
			}
			elseif ($options['zc'] === 'tr') {
				$cropStartX = $newWidth - $width;
				$cropStartY = 0;
			}
			elseif ($options['zc'] === 'l') {
				$cropStartX = 0;
				$cropStartY = (int) (($newHeight - $height) / 2);
			}
			elseif ($options['zc'] === 'r')  {
				$cropStartX = $newWidth - $width;
				$cropStartY = (int) (($newHeight - $height) / 2);
			}
			elseif ($options['zc'] === 'bl')  { $cropStartY = $newHeight - $height; }
			elseif ($options['zc'] === 'b')  {
				$cropStartX = (int) (($newWidth - $width) / 2);
				$cropStartY = $newHeight - $height;
			}
			elseif ($options['zc'] === 'br')  {
				$cropStartX = $newWidth - $width;
				$cropStartY = $newHeight - $height;
			}
			else {
				$cropStartX = (int) (($newWidth - $width) / 2);
				$cropStartY = (int) (($newHeight - $height) / 2);
			}
			$this->cropStart = new Point($cropStartX, $cropStartY);
			$this->cropBox = new Box($width, $height);
			$cropWidth = $width;
			$cropHeight = $height;
			$width = $newWidth;
			$height = $newHeight;
		}

		if ( ($width < $origWidth && $height < $origHeight) || !empty($options['aoe']) ) {
			$this->size = new Box($width, $height);
		}

		if ($debug) {
			$this->debugmessages[] = substr(var_export($options, TRUE), 7, -3);
			$this->debugmessages[] = "\nOriginal - w: $origWidth | h: $origHeight\nNew - w: $width | h: $height" .
				(isset($this->cropBox) ? "\nCrop Box - w: $cropWidth | h: $cropHeight\nCrop Start - x: $cropStartX | y: $cropStartY" : '');
		}
	}
}

public function apply(ImageInterface $image)
{
	if (isset($this->size))  { $image->scale($this->size); }
	return isset($this->cropBox) ? $image->crop($this->cropStart, $this->cropBox) : $image;
}


}