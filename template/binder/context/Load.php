<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Load extends core\argument\Readable implements core\stringable {

  public function asString() {

    $aValues = array();

    foreach ($this->query() as $child) {

      if ($child instanceof core\argument) {

        $mResult = $child->asString();
      }
      else {

        $mResult = $child;
      }

      if ($mResult) {

        $aValues[] = $mResult;
      }
    }

    $sResult = '';

    if ($aValues) {

      $sCalls = join(";\n", $aValues);
      $sResult = "window.addEvent && window.addEvent('domready', function() { $sCalls })";
      $sResult.= " && typeof sylma != 'undefined' && window.addEvent('load', function() { sylma.ui.onWindowLoad(); });";
    }

    return $sResult;
  }
}

