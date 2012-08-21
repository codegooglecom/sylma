<?php

namespace sylma\parser;
use \sylma\core, sylma\parser\languages\common;

require_once('core/module/Filed.php');

abstract class Reflector extends core\module\Filed {

  /**
   *
   * @var php\_window
   */
  private $window;

  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return php\_window
   */
  public function getWindow() {

    if (!$this->window) {

      $this->throwException(t('No window defined'));
    }

    return $this->window;
  }
}
