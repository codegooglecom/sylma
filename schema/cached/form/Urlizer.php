<?php

namespace sylma\schema\cached\form;
use sylma\core, sylma\core\functions\path;

\Sylma::load('/core/functions/Path.php');

class Urlizer extends _String {

  public function validate() {

    $sAlias = $this->getAlias(false);

    $sElement = substr($sAlias, 0, strpos($sAlias, '_'));

    $el = $this->getHandler()->getElement($sElement);
    $this->setValue(path\urlize($el->getValue()));

    return true;
  }
}

