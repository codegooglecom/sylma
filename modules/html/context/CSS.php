<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

/**
 *
 */
class CSS extends parser\context\Basic implements dom\domable {

  public function asDOM() {

    $aResult = array();

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

      $aResult[] = $aStyle;
    }

    $result = $this->createArgument($aResult, \Sylma::read('namespaces/html'))->asDOM();

    //$this->dsp($result);

    return $result;
  }
}

