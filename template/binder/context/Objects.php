<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Objects extends modules\html\context\JS implements core\stringable {

  const PARENT_PATH = 'sylma.tmp';

  protected function parsePath($sPath) {

    if (strpos($sPath, '.') !== false) $aResult = explode('.', $sPath);
    else $aResult = array($sPath);

    return $aResult;
  }

  public function setParentPath($sPath) {

    $this->sPath = $sPath;
  }

  protected function getParentPath() {

    return $this->sPath ? $this->sPath : self::PARENT_PATH;
  }

  public function asString() {

    return 'sylma.ui.load(' . $this->getParentPath() . ', ' . $this->asJSON() . ')';
  }
}

