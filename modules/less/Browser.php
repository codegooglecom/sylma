<?php

namespace sylma\modules\less;
use sylma\core;

class Browser extends core\module\Domed {

  protected $sPrefix = '';

  public function __construct($sPrefix, $aSettings) {

    $this->setPrefix($sPrefix);
    $this->setSettings($aSettings);
  }

  protected function setPrefix($sValue) {

    $this->sPrefix = $sValue;
  }

  protected function getPrefix() {

    return $this->sPrefix;
  }

  public function prefixProperty($sName, array $aSettings) {

    $sResult = '';

    if (isset($aSettings[$this->getPrefix()])) {

      $iMode = $aSettings[$this->getPrefix()];
    }
    else {

      $iMode = $this->read('mode');
    }

    switch ($iMode) {

      case Prefixer::MODE_PREFIX :

        $sResult = '-' . $this->getPrefix() . '-' . $sName;
        break;
    }

    return $sResult;
  }
}
