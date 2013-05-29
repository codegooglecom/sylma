<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql;

class Field extends sql\template\component\Field implements sql\template\pathable {

  protected function reflectApplySelf($sMode = '') {

    if ($result = parent::reflectApplySelf($sMode)) {

      $this->addToQuery();
    }
    else {

      $result = $this->reflectRead();
    }

    return $result;
  }

  protected function addToQuery() {

    $this->getParent()->addElementToQuery($this);
  }

  public function reflectRead() {

    $this->addToQuery();

    return $this->reflectSelf();
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead, $sArguments = '') {

    switch ($sName) {

      case 'format' :

        if (!$reflector = $this->getReflectorStatic()) {

          $this->launchException('No reflector defined', get_defined_vars());
        }

        $aArguments = $this->getParser()->getPather()->parseArguments($sArguments, $sMode, $bRead);
        $result = $reflector->call('format', array($this->reflectRead(), $aArguments));

        break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments);
    }

    return $result;
  }
}

