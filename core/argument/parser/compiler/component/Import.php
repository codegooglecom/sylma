<?php

namespace sylma\core\argument\parser\compiler\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Import extends reflector\component\Foreigner implements common\argumentable {

  protected $sPath = '';

  public function parseRoot(dom\element $el) {

    $file = $this->getSourceFile($el->read());
    $this->setFile($file);

    $importer = $this->getParser()->getImporter();
    $call = $importer->call('import', array((string) $this->getFile()), '\sylma\core\argument');

    $this->setCall($call);
  }

  protected function setCall(common\_call $call) {

    $this->call = $call;
  }

  protected function getCall() {

    return $this->call;
  }

  public function getVar() {

    return $this->getCall()->getVar();
  }

  public function asArgument() {

    return $this->getCall()->asArgument();
  }
}

