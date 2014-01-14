<?php

namespace sylma\modules\cron;
use sylma\core;

class Main extends core\module\Domed {

  public function __construct(core\argument $args = null) {

    $this->setDirectory(__FILE__);
    $this->setArguments($args);
  }

  public function run() {

    foreach ($this->getScript('list') as $task) {

      $this->createTask($task);
    }
  }

  public function createTask(core\argument $arg) {

    $class = $this->getFactory()->findClass('task');

    return $this->create('task', array($arg, $class));
  }
}

