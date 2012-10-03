<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

/**
 *
 */
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

    $aScripts = array();
    $result = null;

    foreach ($this->asArray() as $mValue) {

      $aScript = array(
        '@type' => 'text/javascript',
      );

      if ($mValue instanceof fs\file) {

        $aScript['@src'] = (string) $mValue;
      }
      else {

        $aScript[] = $this->loadString($mValue);
      }

      $aScripts[] = array('script' => $aScript);
    }

    if ($aScripts) $result = $this->createArgument($aScripts, \Sylma::read('namespaces/html'))->asDOM();

    //$this->dsp($result);

    return $result;
  }
}

