<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom;

class Messages extends context\Basic implements dom\domable {

  public function asDOM() {

    $doc = $this->createDocument();
    $doc->addElement('div', null, array(), \Sylma::read('namespaces/html'));

    foreach ($this->asArray() as $sMessage) {

      $doc->add($this->createDocument($sMessage));
    }

    return $doc->getChildren();
  }
}
