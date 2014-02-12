<?php

namespace sylma\device;
use sylma\core, sylma\core\window;

class Windower extends window\Builder {

  const DEVICE_SETTINGS = 'device';

  public function buildWindow(core\request $path, core\argument $exts, $bUpdate = null, $bRun = true) {

    $this->setupDevice();

    return parent::buildWindow($path, $exts, $bUpdate, $bRun);
  }

  protected function setupDevice() {

    $args = \Sylma::get(self::DEVICE_SETTINGS);

    if ($args->read('enable', false)) {

      $device = $this->create('device');
      \Sylma::setManager('device', $device);
      
      $device->setSettings($args);
    }
  }

  protected function testRoute(core\argument $alt, $sCurrent) {

    $bParent = parent::testRoute($alt, $sCurrent);
    $sDevice = $alt->read('device', false);

    return $bParent && (!$sDevice || $this->isDevice($sDevice));
  }

  protected function isDevice($sName) {

    return $this->getManager('device')->isDevice($sName);
  }
}
