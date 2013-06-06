<?php

namespace sylma\core\request;
use sylma\core, sylma\storage\fs;

class Builder extends Basic {

  public function __construct($sPath = '', fs\directory $dir = null) {

    if ($sPath) $this->setPath($this->resolvePath($sPath, $dir));
    $this->loadSettings();
  }

  public function setPath($sPath) {

    return parent::setPath($sPath);
  }

  public function setFile(fs\file $file) {

    return parent::setFile($file);
  }

  protected function extractArguments(&$sPath) {

    $iAssoc = strpos($sPath, '?');
    $aResult = array();

    if ($iAssoc !== false) {

      $sAssoc = substr($sPath, $iAssoc + 1);
      $sPath = substr($sPath, 0, $iAssoc);

      foreach (explode('&', $sAssoc) as $sArgument) {

        $aArgument = explode('=', $sArgument);

        if (count($aArgument) == 1) $aResult[] = $aArgument[0]; // index : only name
        else $aResult[$aArgument[0]] = $aArgument[1]; // assoc : name and value
      }
    }

    return $aResult;
  }

  protected function implodeArguments(array $aArguments) {

    $aResult = array();

    foreach ($aArguments as $sKey => $mToken) {

      if ($mToken) $aResult[] = $mToken[0] . '=' . $mToken[1];
      else $aResult[] = $sKey;
    }

    return $aResult ? '?' . implode('&', $aResult) : '';
  }

  public function asString() {

    $this->setArguments(array());

    if (!$file = $this->getFile('', false)) {

      if (!$sPath = $this->getPath()) {

        $this->launchException('No file neither path to render request');
      }

      $aArguments = $this->extractArguments($sPath);
      $this->setArguments($aArguments);

      $this->setPath($sPath);
      $this->parse();

      $file = $this->asFile(true);
    }

    $sResult = $file->getParent() . '/' . $file->getSimpleName() . $this->implodeArguments($this->getArguments()->query());

    return $sResult;
  }
}

