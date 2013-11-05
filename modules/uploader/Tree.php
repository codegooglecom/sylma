<?php

namespace sylma\modules\uploader;
use sylma\core, sylma\dom, sylma\storage\xml, sylma\parser\languages\common;

class Tree extends xml\tree\Argument {

  protected $reflector;

  const NS = 'http://2013.sylma.org/modules/uploader';
  const NAME = 'root';

  public function parseRoot(dom\element $el = null) {

    $this->setDirectory(__FILE__);

    $this->setNamespace(self::NS);
    $this->setName(self::NAME);

    $this->setArguments('tree.xml');

    if ($el) {

      $this->loadReflector();
    }
  }

  protected function loadReflector() {

    $reflector = $this->createObject();
    $this->setReflector($reflector);

    $this->getWindow()->add($reflector->getInsert());
    $this->getWindow()->add($reflector->call('setExtensions', array($this->getExtensions())));
  }

  protected function getExtensions() {

    return $this->getArgument('extensions')->query();
  }

  protected function setReflector(common\_var $var) {

    $this->reflector = $var;
  }

  protected function getReflector() {

    if (!$this->reflector) {

      $this->launchException('No reflector defined');
    }

    return $this->reflector;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'validate' : $result = $this->getReflector()->call('validate'); break;
      case 'max-size' : $result = ini_get('upload_max_filesize'); break;
      case 'extensions' : $result = implode(', ', $this->getExtensions()); break;
      case 'directory' : $result = $this->reflectDirectory($aArguments); break;
      case 'position' : $result = ''; break;

      default :

        $this->launchException("Unknown function : $sName");
    }

    return $result;
  }

  protected function reflectDirectory(array $aArguments) {

    return $this->getReflector()->call('setDirectory', array($aArguments['directory']))->getInsert();
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false) {

    return $this->getReflector()->call('read', array($sPath));
  }
}

