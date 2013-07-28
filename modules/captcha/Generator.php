<?php

namespace sylma\modules\captcha;
use sylma\core;

class Generator extends core\module\Domed {

  public function __construct() {

    $this->setDirectory(__FILE__);

    $this->setSettings(\Sylma::get('modules/captcha'));
    $this->generate();
  }

  public function generate() {

    // Adapted for The Art of Web: www.the-art-of-web.com
    // Please acknowledge use of this code by including this header.

    $w = 120; // 160
    $ws = 20; // width space
    $h = 48; // 45
    $hs = 20; // height margin

    // initialise image with dimensions of x, y pixels
    $image = imagecreatetruecolor($w, $h);
    $file = $this->getFile($this->read('background'));

    // set background and allocate drawing colours
	$captcha = imagecreatefrompng($file->getRealPath());

    imagealphablending($captcha, true);
    imagesavealpha($captcha , true);

    $background = imagecolorallocate($image, 0x66, 0xCC, 0xFF);
    imagefill($image, 0, 0, $background);

    $linecolor = imagecolorallocate($image, 0x33, 0x99, 0xCC);
    $textcolor1 = imagecolorallocate($image, 0x00, 0x00, 0x00);
    $textcolor2 = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);

    // draw random lines on canvas
    for($i = 0; $i < 8; $i++) {

      imagesetthickness($image, rand(1,3));
      imageline($image, rand(0,$w), 0, rand(0,$w), $h, $linecolor);
    }

    // using a mixture of TTF fonts
    $fonts = array();
    $fonts[] = "DejaVuSerif-Bold.ttf";
    $fonts[] = "DejaVuSans-Bold.ttf";
    $fonts[] = "DejaVuSansMono-Bold.ttf";

    // add random digits to canvas using random black/white colour
    $digit = '';
    $sDirectory = $this->read('fonts');

    for($x = 10; $x <= $w - $ws; $x += $ws) {

      $textcolor = (rand() % 2) ? $textcolor1 : $textcolor2;
      $digit .= ($num = rand(0, 9));

      imagettftext($image, $hs, rand(-$ws,$ws), $x, rand($hs, $h - 3), $textcolor, $sDirectory . '/' . $fonts[array_rand($fonts)], $num);
    }

    $this->setSession($digit);

    ob_start();

    imagepng($image);
    imagedestroy($image);

    return ob_get_clean();
  }

  protected function getSessionKey() {

    return $this->read('session');
  }

  public function __toString() {

    return $this->generate();
  }
}

