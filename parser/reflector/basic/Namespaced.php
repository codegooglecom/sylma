<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

abstract class Namespaced extends core\module\Domed {

  protected $aManagedNS = array();

  protected function setUsedNamespace($sNamespace) {

    $this->aManagedNS[] = $sNamespace;
  }

  public function getUsedNamespaces() {

    return $this->aManagedNS;
  }

  protected function useNamespace($sNamespace) {

    return in_array($sNamespace, $this->aManagedNS);
  }

  protected function setNamespace($sNamespace, $sPrefix = null, $bDefault = true) {

    if ($bDefault) $this->setUsedNamespace($sNamespace);

    return parent::setNamespace($sNamespace, $sPrefix, $bDefault);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = 'Parser : ' . $this->getNamespace();
    return parent::throwException($sMessage, $mSender, $iOffset);
  }
}
