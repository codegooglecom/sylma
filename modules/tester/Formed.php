<?php

namespace sylma\modules\tester;
use sylma\core;

class Formed extends Parser {

  protected function createToken() {

    return new \sylma\schema\cached\form\Token;
  }

  protected function resetToken() {

    $token = $this->createToken();
    $token->reset();
  }

  public function setToken($sValue) {

    $token = $this->createToken();
    $token->savePath($sValue);
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function runQuery($sValue, $iMode = 1) {

    $db = $this->getManager(self::DB_MANAGER)->getConnection(self::DB_CONNECTION);

    if (!$iMode) {

      $result = $db->get($sValue);
    }
    else if ($iMode & 1) {

      $result = $db->query($sValue);
    }
    else if ($iMode & 2) {

      $result = $db->read($sValue);
    }
    else if ($iMode & 4) {

      $result = $db->extract($sValue);
    }
    else if ($iMode & 8) {

      $result = $db->insert($sValue);
    }

    return $result;
  }
}

