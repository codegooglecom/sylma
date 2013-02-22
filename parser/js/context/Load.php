<?php

namespace sylma\parser\js\context;
use sylma\core, sylma\parser\context, sylma\dom;

/**
 *
 */
class Load extends context\Basic implements dom\domable {

  public function asDOM() {

    $aValues = $this->asArray();
    $result = null;

    if ($aValues) {

      $sCalls = join(";\n", $aValues);
      $sContent = "window.addEvent('domready', function() { $sCalls });";

      $doc = $this->createDocument();

      $result = $doc->addElement('script', null, array('type' => 'text/javascript'), \Sylma::read('namespaces/html'));
      $result->set($sContent);
    }

    return $result;
  }

}

?>
