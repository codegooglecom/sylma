<?php

namespace sylma\modules\captcha;
use sylma\core, sylma\schema;

class Type extends schema\cached\form\_String {

  protected $bUsed = false;

  protected function getSessionKey() {

    return \Sylma::read('modules/captcha/session');
  }

  public function validate() {

    $sValue = $this->getSession();

    if ($this->getValue() === $sValue) {

      $bResult = true;
    }
    else {

      $bResult = false;
      $this->addMessage('Code do not match', $this->asAlias());
    }

    return $bResult;
  }
}

