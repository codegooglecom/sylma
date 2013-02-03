<?php

namespace sylma\core\factory;
use sylma\core;

\Sylma::load('/core/functions/Path.php');

class Reflector extends Cached {

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

  protected function lookupClass($sPath, core\argument $args) {

    $args->registerToken(self::DIRECTORY_TOKEN);
    $args->registerToken(self::CLASSBASE_TOKEN);

    $result = parent::lookupClass($sPath, $args);

    $args->unRegisterToken(self::DIRECTORY_TOKEN);
    $args->unRegisterToken(self::CLASSBASE_TOKEN);

    return $result;
  }
}