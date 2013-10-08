<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Load extends modules\html\context\JS implements core\stringable {

  public function asString() {

    $aValues = array();

    foreach ($this->query() as $child) {

      if ($child instanceof core\argument) {

        $aValues[] = $child->asString();
      }
      else {

        $aValues[] = $child;
      }
    }

    $sResult = '';

    if ($aValues) {

      $sCalls = join(";\n", $aValues);
      $sResult = "window.addEvent && window.addEvent('domready', function() { $sCalls })";
      $sResult.= " && window.addEvent('load', function() { sylma.ui.onWindowLoad(); });";
    }

    return $sResult;
  }
}

