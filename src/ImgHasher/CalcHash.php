<?php

/**
 * A simple image hashing and comparison class based upon
 * the following articles found at hackerfactor.com
 * dHash: http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
 */

namespace ImgHasher {

    use Exception;

    const RGB_MAX = 255;

    // Image scaling dimensions for dHash
    const DHASH_WIDTH = 9;
    const DHASH_HEIGHT = 8;

    class CalcHash {

        /**
         * Returns the dHash of the image
         * @param resource $img GD2 image resource to compute hashes for
         * @return int The image hash
         */
        public static function dHash($img) {

            self::_validateImage($img);

            // We need to have 64-bit numbers available for this to work
            if (PHP_INT_SIZE !== 8) {
                throw new Exception('dHash requires 64-bit integer support');
            }

            // The original image is resampled down to 9x8.
            // We'll build our bit hash from this
            $tempImg = imagecreatetruecolor(DHASH_WIDTH, DHASH_HEIGHT);
            imagecopyresampled($tempImg, $img, 0, 0, 0, 0, DHASH_WIDTH, DHASH_HEIGHT, imagesx($img), imagesy($img));

            $retVal = 0;
            for ($y = 0; $y < DHASH_HEIGHT; $y++) {
                for ($x = 0; $x < DHASH_WIDTH; $x++) {
                    $luma = self::_calculateLuminence(imagecolorat($tempImg, $x, $y));
                    // We ignore the first pixel in the row
                    if ($x > 0) {
                        // The value for this pixel is whether it was brighter than the previous
                        // We then put that at bit slot 0
                        $retVal = $retVal << 1;
                        $retVal |= $luma < $lastLuma ? 1 : 0;
                    }
                    $lastLuma = $luma;
                }
            }

            imagedestroy($tempImg);
            unset($tempImg);

            return $retVal;
        }

        /**
         * Returns the RGB dHash of the image
         * @param resource $img GD2 image resource to compute hashes for
         * @return array The dHash for each color component
         */
        public function dHashRGB($img) {

            self::_validateImage($img);

            // We need to have 64-bit numbers available for this to work
            if (PHP_INT_SIZE !== 8) {
                throw new Exception('dHash requires 64-bit integer support');
            }

            // The original image is resampled down to 9x8.
            // We'll build our bit hash from this
            $tempImg = imagecreatetruecolor(DHASH_WIDTH, DHASH_HEIGHT);
            imagecopyresampled($tempImg, $img, 0, 0, 0, 0, DHASH_WIDTH, DHASH_HEIGHT, imagesx($img), imagesy($img));

            $hashR = 0;
            $hashG = 0;
            $hashB = 0;
            for ($y = 0; $y < DHASH_HEIGHT; $y++) {
                for ($x = 0; $x < DHASH_WIDTH; $x++) {
                    $pixel = imagecolorat($tempImg, $x, $y);
                    $r = ($pixel >> 16) & 0xff;
                    $g = ($pixel >> 8) & 0xff;
                    $b = $pixel & 0xff;

                    // We ignore the first pixel in the row
                    if ($x > 0) {
                        // The value for this pixel is whether it was brighter than the previous
                        // We then put that at bit slot 0
                        $hashR = $hashR << 1;
                        $hashR |= $r < $lastR ? 1 : 0;
                        $hashG = $hashG << 1;
                        $hashG |= $g < $lastG ? 1 : 0;
                        $hashB = $hashB << 1;
                        $hashB |= $b < $lastB ? 1 : 0;
                    }
                    $lastR = $r;
                    $lastG = $g;
                    $lastB = $b;
                }
            }

            imagedestroy($tempImg);
            unset($tempImg);

            return [ 'r' => $hashR, 'g' => $hashG, 'b' => $hashB ];
        }

        /**
         * Given an RGB color, calculates it's luminence value
         * @param int $color The 32-bit color
         * @return float The luminence value of the color
         */
        private static function _calculateLuminence($color) {
            $r = ($color >> 16) / RGB_MAX;
            $g = (($color >> 8) & 0xff) / RGB_MAX;
            $b = ($color & 0xff) / RGB_MAX;
            return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        }


        /**
         * Ensures that $img is a valid GD2 resource
         * @param resource $img The value to check
         * @return boolean Returns true or throws an error if the resource is invalid
         */
        private static function _validateImage($img) {
            if (!$img || false === imagesx($img)) {
                throw new Exception('Invalid image');
            }
            return true;
        }

    }

}