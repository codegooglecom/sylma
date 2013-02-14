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

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();

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

      $result = $this->loadParser($sNamespace);
    }

    return $result;
  }

  /**
   * Set local parsers, with associated namespaces
   * @param parser\reflector\domed $parser
   * @param array $aNS
   */
  protected function addParser(parser\reflector\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }

  protected function lookupParserForeign($sNamespace) {

    if ($this->getParent()) {

      return $this->getParent()->lookupParser($sNamespace);
    }
    else {

      $result = $this->loadParser($sNamespace);
    }

    return $result;
  }

  /**
   * Exception free parser loader
   *
   * @param string $sNamespace
   * @return parser\domed
   */
  protected function loadParser($sNamespace) {

    if (array_key_exists($sNamespace, $this->aParsers)) {

      $result = $this->aParsers[$sNamespace];
    }
    else {

      $result = $this->createParser($sNamespace);

      if ($result) {

        $this->addParser($result, $result->getUsedNamespaces());
      }
    }

    //if ($result) $result->setParent($this);

    return $result;
  }

  protected function createParser($sNamespace) {

    $manager = $this->getManager('parser');
    return $manager->getParser($sNamespace, $this->getRoot(), $this, false);
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
