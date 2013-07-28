<?php

namespace sylma\core\factory;
use sylma\core;

\Sylma::load('/core/functions/Path.php');

class Reflector extends Cached {

  protected static $sArgumentClass = 'sylma\core\argument\Filed';

  public function __construct(core\argument $classes = null) {

    $this->setArguments($this->createArgument(array()));
    if ($classes) $this->setArguments($classes);
  }

  protected function loadClassBase(core\argument $class) {

    if ($sClassBase = $this->getArguments()->getToken(self::CLASSBASE_TOKEN)) {

      $class->set('name', core\functions\path\toAbsolute($class->read('name'), $sClassBase, '\\'));
    }
  }

  protected function loadFileBase(core\argument $class, $sDirectory = '') {

    if ($sInlineDirectory = $this->getArguments()->getLastDirectory()) $sDirectory = $sInlineDirectory;

    if ($sFile = $class->read('file', false)) {

      $class->set('file', core\functions\path\toAbsolute($sFile, $sDirectory));
    }
  }

  protected function lookupClass($sPath, core\argument $args, $bDebug = true) {

    $args->registerToken(self::DIRECTORY_TOKEN);
    $args->registerToken(self::CLASSBASE_TOKEN);

    $result = parent::lookupClass($sPath, $args, $bDebug);

    $args->unRegisterToken(self::DIRECTORY_TOKEN);
    $args->unRegisterToken(self::CLASSBASE_TOKEN);

    return $result;
  }
}