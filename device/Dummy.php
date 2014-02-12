<?php

namespace sylma\device;
use sylma\core;

\Sylma::load('lib/Mobile_Detect.php', __DIR__);

/**
 * Use Mobile-Detect : https://github.com/serbanghita/Mobile-Detect
 */
class Dummy extends \Mobile_Detect
{

  protected $settings;

  public function setSettings(core\argument $settings) {

    $this->settings = $settings;
  }

  protected function getSettings() {

    return $this->settings;
  }

  public function isDevice($sName) {

    $sForce = $this->getSettings()->read('force', false);

    switch ($sName) {

      case 'mobile' : $bResult = $sForce === 'mobile' || (!$sForce && $this->isMobile()); break;
      case 'tablet' : $bResult = $sForce === 'tablet' || (!$sForce && $this->isTablet()); break;
      case 'desktop' : $bResult = $sForce === 'desktop' || (!$sForce && !$this->isMobile() && !$this->isTablet()); break;

      default :

        $bResult = false;

    }

    return $bResult;
  }
}
