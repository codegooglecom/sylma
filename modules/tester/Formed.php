<?php

namespace sylma\modules\tester;
use sylma\core;

class Formed extends Profiler {

  const MODE_GET = 0;
  const MODE_QUERY = 1;
  const MODE_READ = 2;
  const MODE_EXTRACT = 4;
  const MODE_INSERT = 8;
  const MODE_DELETE = 8;

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

  public function runQueryFile($sPath) {

    return $this->runQuery($this->getFile($sPath)->read(), self::MODE_INSERT);
  }

  public function runQuery($sValue, $iMode = self::MODE_QUERY, $sConnection = self::DB_CONNECTION) {

    $db = $this->getManager(self::DB_MANAGER)->getConnection($sConnection);

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

