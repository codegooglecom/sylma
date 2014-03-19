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

  protected function testRoute(core\argument $alt, $sCurrent, $iKey) {

    $iWeight = parent::testRoute($alt, $sCurrent, $iKey);

    if ($iWeight) {

      $sDevice = $alt->read('device', false);

      if ($sDevice && !$this->isDevice($sDevice)) {

        $iWeight = 0;
      }
    }

    return $iWeight;
  }

  protected function isDevice($sName) {

    return $this->getManager('device')->isDevice($sName);
  }
}
