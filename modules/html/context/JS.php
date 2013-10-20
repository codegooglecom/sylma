<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\dom, sylma\storage\fs;

class JS extends Basic implements dom\domable {

  const EXTENSION = 'js';

  protected function addText($mValue) {

    if (is_string($mValue)) {

      $sValue = $mValue;
    }
    else if ($mValue instanceof core\stringable) {

      $sValue = $mValue->asString();
    }
    else {

      $this->launchException("Unknow value type : " . \Sylma::show($mValue));
    }

    return array('script' => array(
      '@type' => 'text/javascript',
      $sValue,
    ));
  }

  protected function addFile(fs\file $file, $bReal = false) {

    return array('script' => array(
      '@src' => $bReal ? '/' . $file->getRealPath() : (string) $file,
      '@type' => 'text/javascript',
    ));
  }

  public function asDOM() {

    $aStrings = $this->loadContent();

    if ($aStrings = $this->loadContent()) {

      $bChildren = false;
      $doc = $this->buildDocument($aStrings, \Sylma::read('namespaces/html'), $bChildren);
      $result = $bChildren ? $doc->getChildren(): $doc->getRoot();
    }

    return $result;
  }
}

