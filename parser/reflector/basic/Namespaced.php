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

  public function useNamespace($sNamespace) {

    return in_array($sNamespace, $this->aManagedNS);
  }

  protected function setNamespace($sNamespace, $sPrefix = null, $bDefault = true) {

    if ($bDefault) $this->setUsedNamespace($sNamespace);

    return parent::setNamespace($sNamespace, $sPrefix, $bDefault);
  }
}
