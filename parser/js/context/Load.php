<?php

namespace sylma\parser\js\context;
use sylma\core, sylma\parser, sylma\dom;

/**
 *
 */
class Load extends parser\context\Basic implements dom\domable {

  public function asDOM() {

    $sCalls = join(";\n", $this->asArray());

    $aScript = array('script' => array(
      '@type' => 'text/javascript',
      "window.addEvent('domready', function() { $sCalls });",
    ));

    $result = $this->createArgument($aScript, \Sylma::read('namespaces/html'))->asDOM();

    return $result;
  }

}

?>
