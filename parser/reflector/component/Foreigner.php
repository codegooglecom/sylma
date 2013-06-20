<?php

namespace sylma\parser\reflector\component;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Foreigner extends reflector\basic\Foreigner implements reflector\component {

  const PREFIX = 'self';

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $allowComponent = false;
  protected $parser;
  protected $sourceFile;

  public function __construct(reflector\domed $parser, core\argument $arg = null, array $aNamespaces = array()) {

    //$this->allowComponent($bComponent);
    //$this->allowForeign($bForeign);
    //$this->allowUnknown($bUnknown);

    $this->setParser($parser);
    $this->setArguments($arg);

    $this->setUsedNamespaces($aNamespaces);

    //$this->setNamespace($el->getNamespace());
    //$this->parseRoot($el);
  }

  public function parseRoot(dom\element $el) {

    $this->throwException('No root instructions');
    //return $this->parseComponentRoot($el);
  }

  protected function loadElementForeignKnown(dom\element $el, reflector\elemented $parser) {

    return $this->getParser()->loadElementForeignKnown($el, $parser);
  }

  protected function lookupParserForeign($sNamespace) {

    if (!$result = $this->getParser()->lookupParser($sNamespace)) {

      $result = $this->getParser()->createParser($sNamespace);
    }

    return $result;
  }

  protected function parseComponent(dom\element $el) {

    if ($this->allowComponent()) {

      $result = $this->loadComponent($el->getName(), $el, $this->getParser());
    }
    else {

      $result = $this->getParser()->parseComponent($el, $this->getParser());
    }

    return $result;
  }

  protected function loadComponent($sName, dom\element $el, $manager = null) {

    if (!$this->allowComponent()) {

      $result = $this->getParser()->loadComponent($sName, $el);
    }
    else {

      $result = parent::loadComponent($sName, $el, $this->getParser());
    }

    return $result;
  }

  protected function loadSimpleComponent($sName, $manager = null) {

    if (!$this->allowComponent()) {

      $result = $this->getParser()->loadSimpleComponent($sName);
    }
    else {

      $result = parent::loadSimpleComponent($sName, $this->getParser());
    }

    return $result;
  }

  protected function importComponent(reflector\component $component) {

    return $this->getParser()->importComponent($component);
  }

  protected function setParser(reflector\domed $parent) {

    $this->parser = $parent;
  }

  protected function getParser() {

    return $this->parser;
  }

  protected function getRoot() {

    return $this->getParser()->getRoot();
  }

  protected function parseComponentRoot(dom\element $el, $bDebug = true) {

    $children = $el->getChildren();

    if (!$children->length) {

      if ($bDebug) $this->launchException('Empty component not allowed', get_defined_vars());
      $mResult = null;
    }
    else {

      if ($children->length > 1) {

        $mResult = $this->parseChildren($children);
      }
      else {

        $mResult = $this->parseNode($el->getFirst());
      }
    }

    return $mResult;
  }

    /**
   * @return \sylma\parser\languages\common\_window
   */
  protected function getWindow() {

    return $this->getParser()->getWindow();
  }

  protected function getElementFile(dom\element $el, $sPath, $bFile = true) {

    if ($sSource = $this->readx('@build:source', false, array('build' => self::BUILDER_NS))) {

      $tmp = $this->getParser()->getSourceFile();
      $manager = $tmp->getControler();

      $file = $this->getParser()->getSourceFile($sSource);

      if ($sPath) {

        $result = $bFile ? $manager->getFile($sPath, $file->getParent()) : $manager->getDirectory($sPath, $file->getParent());
      }
    }
    else {

      $result = null;
    }

    return $result;
  }

  protected function loadSourceFile() {

    if (is_null($this->sourceFile)) {

      if ($this->getNode(false) and $sSource = $this->readx('@build:source', false, array('build' => self::BUILDER_NS))) {

        $result = $this->getParser()->getSourceFile($sSource);
      }
      else {

        $result = false;
      }

      $this->sourceFile = is_null($result) ? false : $result;
    }

    return $this->sourceFile;
  }

  public function getSourceDirectory($sPath = '') {

    if ($source = $this->loadSourceFile()) {

      $manager = $this->getManager(self::FILE_MANAGER);
      $result = $sPath ? $manager->getDirectory($sPath, $source->getParent()) : $source->getParent();
    }
    else {

      $result = $this->getParser()->getSourceDirectory($sPath);
    }

    return $result;
  }

  public function getSourceFile($sPath = '', $bElement = true) {

    if ($bElement and $source = $this->loadSourceFile()) {

      $manager = $this->getManager(self::FILE_MANAGER);
      $result = $sPath ? $manager->getFile($sPath, $source->getParent()) : $source;
    }
    else {

      $result = $this->getParser()->getSourceFile($sPath);
    }

    return $result;
  }

  protected function reflectEscape($content) {

    return array("'", $this->getWindow()->callFunction('addslashes', 'php-string', array($content)), "'");
  }

  protected function log($sMessage = '', array $aVars = array()) {

    $this->startLog($sMessage, $aVars);
    $this->stopLog();
  }

  protected function startLog($sMessage = '', array $aVars = array()) {

    $this->getParser()->startComponentLog($this, $sMessage, array_merge(array(
      //'node' => $this->getNode(),
    ), $aVars));
  }

  protected function stopLog() {

    $this->getParser()->stopComponentLog();
  }
}

