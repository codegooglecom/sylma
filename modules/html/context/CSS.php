<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\storage\fs;

class CSS extends context\Basic implements dom\domable {

  public function asDOM() {

    $result = null;
    $aStyles = array();
    $aFiles = array();

    foreach ($this->asArray() as $mValue) {

      if ($mValue instanceof fs\file) {

        $sFile = (string) $mValue;

        if (!array_key_exists($sFile, $aFiles)) {

          $aStyle = array('link' => array(
            '@href' => $sFile,
            '@rel' => 'stylesheet',
            '@type' => 'text/css',
            '@media' => 'all',
          ));

          $aFiles[$sFile] = true;
        }
        else {

          $aStyle = array();
        }
      }
      else {

        $aStyle = array('style' => array(
          '@type' => 'text/css',
          $mValue,
        ));
      }

      if ($aStyle) $aStyles[] = $aStyle;
    }

    if ($aStyles) $result = $this->createArgument($aStyles, \Sylma::read('namespaces/html'))->asDOM();

    return $result;
  }
}

