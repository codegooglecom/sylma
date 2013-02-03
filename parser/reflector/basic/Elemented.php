<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector;

abstract class Elemented extends Master {

  const ARGUMENTS = '';

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  public function __construct(reflector\domed $parent, core\argument $arg = null) {

    $this->setNamespace(static::NS);

    if ($arg && $sDirectory = $arg->read('directory', null, false)) {

      $dir = $this->getManager(self::FILE_MANAGER)->getDirectory($sDirectory);
      $this->setDirectory($dir);
    }

    if (!$sArguments = static::ARGUMENTS) {

      $sArguments = $arg->read('arguments', null, false);
    }

    if ($sArguments && $this->getDirectory('', false)) {

      $manager = $this->getManager(static::ARGUMENT_MANAGER);
      $this->setArguments($manager->createArguments($this->getFile($sArguments)));
    }

    $this->setParent($parent);
    if ($arg) $this->setArguments($arg);
  }

}
