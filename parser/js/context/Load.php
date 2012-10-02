<?php

namespace sylma\parser\js\context;
use sylma\core, sylma\parser, sylma\dom;

/**
 *
 */
class Load extends parser\context\Basic implements dom\domable {

  public function asDOM() {

    $aValues = $this->asArray();
    $result = null;

    if ($aValues) {

      $sCalls = join(";\n", $aValues);

      $aScript = array('script' => array(
        '@type' => 'text/javascript',
        "window.addEvent('domready', function() { $sCalls });",
      ));

      $result = $this->createArgument($aScript, \Sylma::read('namespaces/html'))->asDOM();
    }

    return $result;
  }

}

?>
