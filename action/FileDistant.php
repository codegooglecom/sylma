<?php

namespace sylma\action;
use sylma\core, sylma\storage\fs;

class FileDistant extends fs\basic\File
{

  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), $iDebug = 0) {

    $this->sFullPath = $sName;
  }

  public function getParent() {

    $this->throwException('No parent defined');
  }
}
