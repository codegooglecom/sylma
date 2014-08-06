<?php

namespace sylma\modules\uploader;
use sylma\core, sylma\dom, sylma\storage\xml, sylma\parser\languages\common;

class Tree extends xml\tree\Argument {

  protected $reflector;

  const NS = 'http://2013.sylma.org/modules/uploader';
  const NAME = 'root';

  protected $aExtensions = array();

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
  }

  protected function setExtensions(array $aValues) {

    $this->aExtensions = $aValues;
  }

  protected function getExtensions() {

    return $this->aExtensions;//$this->getArgument('extensions')->query();
  }

  protected function setReflector(common\_var $var) {

    $this->reflector = $var;
  }

  protected function getReflector($bDebug = true) {

    if (!$this->reflector && $bDebug) {

      $this->launchException('No reflector defined');
    }

    return $this->reflector;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'init' : $result = $this->reflectFunctionInit($this->getParser()->getPather()->parseArguments($sArguments, $sMode, $bRead, false)); break;
      case 'validate' : $result = $this->reflectFunctionValidate($this->getParser()->getPather()->parseArguments($sArguments, $sMode, $bRead, false)); break;
      case 'max-size' : $result = ini_get('upload_max_filesize') . 'B'; break;
      //case 'extensions' : $result = implode(', ', $this->getExtensions()); break;
      case 'extensions' : $result = $this->reflectFunctionExtensions($aPath, $sMode, $aArguments); break;
      case 'directory' : $result = $this->reflectDirectory($aArguments); break;
      case 'position' : $result = ''; break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  protected function reflectSetExtensions() {

    return $this->getReflector()->call('setExtensions', array($this->getExtensions()));
  }

  protected function reflectFunctionInit(array $aArguments) {

    $this->setExtensions($aArguments);

    return $this->getReflector(false) ? $this->reflectSetExtensions() : null;
  }

  protected function reflectFunctionValidate(array $aArguments) {

    return $this->getReflector()->call('validate', $aArguments);
  }

  protected function reflectFunctionExtensions(array $aPath, $sMode, array $aArguments) {

    $this->loadDefaultSettings();

    if (!$aExtensions = $this->getExtensions()) {

      $this->launchException('No extensions defined');
    }

    $root = $this->createArgument(array('#extension' => $aExtensions))->asDOM();
    $parser = $this->getParser();
    $aResult = array();

    foreach ($root->getChildren() as $ext) {

      $ext = $this->loadChild($this->createOptions($this->createDocument($ext)));
      $aResult[] = $parser->applyPathTo($ext, $aPath, $sMode, $aArguments);
    }

    return $aResult;
  }

  protected function reflectDirectory(array $aArguments) {

    return $this->getReflector()->call('setDirectory', array($aArguments['directory']))->getInsert();
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false, array $aArguments = array()) {

    return $this->getReflector()->call('read', array($sPath));
  }
}

