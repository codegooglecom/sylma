<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\storage\fs;

class JS extends context\Basic implements dom\domable {

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
    $aFiles = array();

    foreach ($this->asArray() as $mValue) {

      if ($mValue instanceof fs\file) {

        $sFile = (string) $mValue;

        if (!array_key_exists($sFile, $aFiles)) {

          $this->buildElement($doc)->setAttribute('src', $sFile);
          $aFiles[$sFile] = true;
        }
      }
      else {

        $this->buildElement($doc)->set($this->loadString($mValue));
        //$el->set($doc->createCData($this->loadString($mValue)));
      }
    }

    return $doc->getChildren();
  }

  protected function buildElement(dom\document $doc) {

    return $doc->addElement('script', null, array('type' => 'text/javascript'), \Sylma::read('namespaces/html'));
  }
}

