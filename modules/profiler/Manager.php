<?php

namespace sylma\modules\profiler;
use sylma\core;

/**
 * Require XHProf : https://php.net/manual/fr/book.xhprof.php
 */
class Manager extends core\module\Domed {

  const ROOT = 'xhprof/';

  protected $aResult = array();

  public function start() {

    \xhprof_enable(\XHPROF_FLAGS_CPU + \XHPROF_FLAGS_MEMORY);
  }

  public function stop() {

    $this->aResult = xhprof_disable();
  }

  public function save() {

    if (!$this->aResult) {

      $this->launchException('Cannot save profile, no result found');
    }

    require_once(self::ROOT . '/utils/xhprof_lib.php');
    require_once(self::ROOT . '/utils/xhprof_runs.php');

    $xhprof_runs = new \XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($this->aResult, "xhprof_testing");
  }
}
