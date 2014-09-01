<?php

namespace sylma\modules\tester\stepper;
use sylma\core, sylma\modules\stepper, sylma\modules\tester, sylma\storage\fs;

class Dummy extends stepper\Browser {

  const NS = 'http://2013.sylma.org/modules/stepper';
  const DIRECTORY = '/test/tmp';

  protected function buildChild(core\argument $child) {

    if ($child->getName() === 'module') {

      $aResult = $this->buildModule($child);
    }
    else {

      $aResult = parent::buildChild($child);
    }

    return $aResult;
  }

  protected function buildModule(core\argument $module) {

    return array(
      'dummy' => $module->read('@dummy'),
      '_alias' => 'module',
    );
  }

  protected function createHandler($sClass) {

    return $this->checkHandler(new $sClass);
  }

  protected function checkHandler(tester\Stepper $handler) {

    return $handler;
  }

  public function getModuleFiles($sClass) {

    $handler = $this->createHandler($sClass);

    foreach ($handler->getFiles() as $file) {

      $aResult[] = array(
        'path' => (string) $file,
        'name' => $file->getName(),
      );
    }

    return array('file' => $aResult);
  }

  public function getModuleTests($sClass, fs\file $file) {

    $handler = $this->createHandler($sClass);

    return $handler->getTests($file);
  }

  public function testModule($sClass, fs\file $file, $sID) {

    $handler = $this->createHandler($sClass);

    return $handler->testModule($file, $sID);
  }
}

