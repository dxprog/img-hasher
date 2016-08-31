<?php

namespace phpUnitTutorial\Test {

  use Exception;
  use ImgHasher;

  function loadImage() {
    return imagecreatefrompng('./tests/color-bars.png');
  }

  class CalcImageTest extends \PHPUnit_Framework_TestCase
  {

    function testdHash() {

      // Calling without a valid image should throw an error
      try {
        ImgHasher\CalcHash::dHash(null);
        $this->assertTrue(false);
      } catch (Exception $e) {
        $this->assertEquals($e->getMessage(), 'Invalid image', 'Exception thrown for invalid image');
      }

      // Instantiating with a valid image should work
      $img = loadImage();
      $hash = ImgHasher\CalcHash::dHash($img);
      $this->assertEquals(9114861776524122264, $hash, 'Image hash is as expected');

    }

    function testDHashRGB() {

      // Calling without a valid image should throw an error
      try {
        ImgHasher\CalcHash::dHash(null);
        $this->assertTrue(false);
      } catch (Exception $e) {
        $this->assertEquals($e->getMessage(), 'Invalid image', 'Exception thrown for invalid image');
      }

      // Instantiating with a valid image should work
      $img = loadImage();
      $hash = ImgHasher\CalcHash::dHashRGB($img);

      $this->assertEquals([
        'r' => 3906369333261080728,
        'g' => 1736164150268237976,
        'b' => 6148914691235223704
      ], $hash, 'Image hash is as expected');

    }

  }

}