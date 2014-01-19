<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs;

class Profiler extends Parser {

  protected $profiler;

  protected function parseResult(dom\element $test, fs\file $file, array $aArguments = array()) {

    if ($test->readx('@profile', array(), false)) {

      $this->loadProfiler();
      $this->startProfile();
    }

    return parent::parseResult($test, $file, $aArguments);
  }

  protected function loadResult($manager, fs\file $file, array $aArguments, $bDelete = true) {

    $result = parent::loadResult($manager, $file, $aArguments, $bDelete);

    if ($this->profiler) {

      $this->stopProfile();
      $this->profiler = null;
    }

    return $result;
  }

  public function loadProfiler() {

    $this->profiler = new \sylma\modules\profiler\Manager();
  }

  public function startProfile() {

    $this->profiler->start();
  }

  public function stopProfile() {

    $this->profiler->stop();
    $this->profiler->save();
  }
}

