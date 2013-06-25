<?php

namespace sylma\modules\menus;
use sylma\core;

class Cached extends core\module\Managed {

  protected $path;

  public function __construct() {

    $this->setPath($this->getManager('path'));
  }

  protected function setPath(core\request $path) {

    $this->path = $path;
  }

  protected function getPath() {

    return (string) $this->path;
  }

  public function checkActive($sPath) {

    if ($sPath === '/') {

      $bResult = $this->getPath() === '/';
    }
    else {

      $bResult = preg_match("`^$sPath`", $this->getPath());
    }
    
    return  $bResult ? 'sylma-highlight' : '';
  }
}

