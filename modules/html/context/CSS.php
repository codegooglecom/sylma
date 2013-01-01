<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

/**
 *
 */
class CSS extends parser\context\Basic implements dom\domable {

  public function asDOM() {

    $result = null;
    $aStyles = array();

    foreach ($this->asArray() as $mValue) {

      if ($mValue instanceof fs\file) {

        $aStyle = array('link' => array(
          '@href' => (string) $mValue,
          '@rel' => 'stylesheet',
          '@type' => 'text/css',
          '@media' => 'all',
        ));
      }
      else {

        $aStyle = array('style' => array(
          '@type' => 'text/css',
          $mValue,
        ));
      }

      $aStyles[] = $aStyle;
    }

    if ($aStyles) $result = $this->createArgument($aStyles, \Sylma::read('namespaces/html'))->asDOM();

    return $result;
  }
}

