<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector;

abstract class Elemented extends reflector\basic\Foreigner {

  const ARGUMENTS = '';
  const PREFIX = 'self';

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $allowComponent = true;

  protected $root;
  protected $parent;

  public function __construct($manager, reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    $this->setManager($manager);
    $this->setRoot($root);
    if ($parent) $this->setParent($parent);

    $this->loadNamespace();
    if ($arg) $this->loadDirectory($arg);
    $this->loadArguments($arg);

    if ($arg) $this->setArguments($arg);
  }

  protected function setParent(reflector\elemented $parent) {

    if ($parent === $this) {

      $this->throwException('Cannot set itself as parent');
    }

    //if ($this->getParent()) $this->throwException('Cannot set parent twice');

    $this->parent = $parent;
  }

  protected function getParent() {

    return $this->parent;
  }

  protected function setRoot(reflector\documented $root) {

    $this->root = $root;
  }

  public function getRoot() {

    return $this->root;
  }

  protected function loadNamespace() {

    if (!$this->getNamespace()) {

      $this->setNamespace(static::NS, static::PREFIX);
    }
  }

  protected function loadDirectory(core\argument $arg) {

    if ($arg and $sDirectory = $arg->read('directory', null, false)) {

      $dir = $this->getManager(self::FILE_MANAGER)->getDirectory($sDirectory);
      $this->setDirectory($dir);
    }
  }

  protected function loadArguments(core\argument $arg = null) {

    if (!$sArguments = static::ARGUMENTS) {

      if ($arg) $sArguments = $arg->read('arguments', null, false);
    }

    if ($sArguments && $this->getDirectory('', false)) {

      $manager = $this->getManager(static::ARGUMENT_MANAGER);
      $this->setArguments($manager->createArguments($this->getFile($sArguments)));
    }
  }

  /**
   * Get a file relative to the source file's directory
   * @param string $sPath
   * @return fs\file
   */
  protected function getSourceFile($sPath) {

    return $this->getManager(static::FILE_MANAGER)->getFile($sPath, $this->getRoot()->getSourceDirectory());
  }

  public function parseFromChild(dom\element $el) {

    return $this->parseElementSelf($el);
  }

  public function parseComponent(dom\element $el) {

    if (!$this->allowComponent()) {

      $this->throwException(sprintf('Component building not allowed with %s', $el->asToken()));
    }

    return $this->createComponent($el, $this);
  }

  public function lookupParser($sNamespace) {

    $result = null;

    if ($this->useNamespace($sNamespace)) {

      $result = $this;
    }
    else {

      //$result = $this->loadParser($sNamespace);
    }

    return $result;
  }

  protected function lookupParserForeign($sNamespace) {

    $result = null;

    if ($this->getParent()) {

      return $this->getParent()->lookupParser($sNamespace);
    }
    else {

      //$result = $this->loadParser($sNamespace);
    }

    return $result;
  }

  public function getLastElement() {

    return parent::getLastElement();
  }

  public function getWindow() {

    return $this->getRoot()->getWindow();
  }

  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }
}
