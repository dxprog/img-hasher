<?php

/**
 * A simple image hashing and comparison class based upon
 * the following articles found at hackerfactor.com
 * aHash: http://www.hackerfactor.com/blog/index.php?/archives/432-Looks-Like-It.html
 * dHash: http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
 */

namespace ImgHasher {

    const RGB_MAX = 255;

    // Image scaling dimensions for dHash
    const DHASH_WIDTH = 9;
    const DHASH_HEIGHT = 8;

    class HashedImg {

        private $_img = null;

        // The cached dHash value
        private $_dHash = null;

        /**
         * Constructor
         * @param resource $img GD2 image resource to compute hashes for
         */
        public function __construct($img) {
            $this->_validateImage($img);
            $this->_img = $img;
        }

        public function aHash($img) {
            // TODO
        }

        /**
         * Returns the dHash of the image
         * @return int The image hash
         */
        public function dHash() {

            // Return the cached dHash value if it's there
            if ($this->_dHash) {
                return $this->_dHash;
            }

            $img = $this->_img;

            // We need to have 64-bit numbers available for this to work
            if (PHP_INT_SIZE !== 8) {
                throw new Exception('dHash requires 64-bit integer support');
            }

            // The original image is resampled down to 9x8.
            // We'll build our bit hash from this
            $tempImg = imagecreatetruecolor(9, 8);
            imagecopyresampled($tempImg, $img, 0, 0, 0, 0, DHASH_WIDTH, DHASH_HEIGHT, imagesx($img), imagesy($img));

            $retVal = 0;
            for ($y = 0; $y < DHASH_HEIGHT; $y++) {
                for ($x = 0; $x < DHASH_WIDTH; $x++) {
                    $luma = $this->_calculateLuminence(imagecolorat($tempImg, $x, $y));
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
         * Performs a dHash compare between this image and another image
         * @param mixed $img A GD2 resource, HashedImg object, or numeric hash
         * @return int The distance between the two images
         */
        public function dHashCompare($img) {
            if ($img instanceof HashedImg) {
                $dHash = $img->dHash();
            } else if (is_numeric($img)) {
                $dHash = $img;
            } else if ($this->_validateImage($img)) {
                $tempHash = new HashedImg($img);
                $dHash = $tempHash->dHash();
                unset($tempHash);
            } else {
                throw new Exception('Invalid image or hash');
            }
            return $this->_calculateHammingDistance($this->dHash(), $dHash);
        }

        /**
         * Given an RGB color, calculates it's luminence value
         * @param int $color The 32-bit color
         * @return float The luminence value of the color
         */
        private function _calculateLuminence($color) {
            $r = ($color >> 16) / RGB_MAX;
            $g = (($color >> 8) & 0xff) / RGB_MAX;
            $b = ($color & 0xff) / RGB_MAX;
            return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        }

        /**
         * Calculates the Hamming distance between two numbers:
         *   http://en.wikipedia.org/wiki/Hamming_distance
         * @param int $hash1 The first image hash
         * @param int $hash2 The second image hash
         * @return int The distance between the two numbers
         */
        private function _calculateHammingDistance($hash1, $hash2) {
            $retVal = 0;
            for ($i = 0, $count = PHP_INT_SIZE * 8; $i < $count; $i++) {
                $retVal += (($hash1 >> $i) & 0x1) ^ (($hash2 >> $i) & 0x1);
            }
            return $retVal;
        }

        /**
         * Ensures that $img is a valid GD2 resource
         * @param resource $img The value to check
         * @return boolean Returns true or throws an error if the resource is invalid
         */
        private function _validateImage($img) {
            if (!$img || false === imagesx($img)) {
                throw new Exception('Invalid image');
            }
            return true;
        }

    }

}