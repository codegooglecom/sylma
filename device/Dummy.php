<?php

namespace sylma\device;
use sylma\core;

\Sylma::load('lib/Mobile_Detect.php', __DIR__);

/**
 * Use Mobile-Detect : https://github.com/serbanghita/Mobile-Detect
 */
class Dummy extends \Mobile_Detect
{

  public function isDevice($sName) {

    switch ($sName) {

      case 'mobile' : $bResult = $this->isMobile(); break;
      case 'tablet' : $bResult = $this->isTablet(); break;
      case 'desktop' : $bResult = !$this->isMobile() && !$this->isTablet(); break;

      default :

        $bResult = false;

    }

    return $bResult;
  }
}
