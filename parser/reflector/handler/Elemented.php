<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Elemented extends Parsed {

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

  public function parseComponent(dom\element $el) {

    return parent::parseComponent($el);
  }

  protected function loadDirectory(core\argument $arg) {

    if ($arg and $sDirectory = $arg->read('directory', null, false)) {

      $dir = $this->getManager(self::FILE_MANAGER)->getDirectory($sDirectory);
      $this->setDirectory($dir);
    }
  }

  protected function loadArguments(core\argument $arg = null) {

    if ($sArguments = static::ARGUMENTS) {

      if ($this->getDirectory('', false)) {

        $manager = $this->getManager(static::ARGUMENT_MANAGER);
        $this->setArguments($manager->createArguments($this->getFile($sArguments)));
      }
    }
    else if ($arg and $sArguments = $arg->read('arguments', null, false)) {

       $this->setArguments($sArguments);
    }

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
