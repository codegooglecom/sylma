<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

class JS extends parser\context\Basic implements dom\domable {

  protected function loadString($mValue) {

    if (is_string($mValue)) {

      $sResult = $mValue;
    }
    else if ($mValue instanceof core\stringable) {

      $sResult = $mValue->asString();
    }

    return $sResult;
  }

  public function asDOM() {

    $doc = $this->createDocument('root');
    $sNamespace = \Sylma::read('namespaces/html');

    foreach ($this->asArray() as $mValue) {

      $el = $doc->addElement('script', null, array('type' => 'text/javascript'), $sNamespace);

      if ($mValue instanceof fs\file) {

        $el->setAttribute('src', (string) $mValue);
      }
      else {

        $el->set($this->loadString($mValue));
        //$el->set($doc->createCData($this->loadString($mValue)));
      }
    }

    return $doc->getChildren();
  }
}

