<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\storage\sql, sylma\parser\reflector, sylma\template;

class Handler extends sql\schema\Handler {

  protected $var;
  protected $query;
  protected $template;

  public function getView() {

    return $this->view;
  }

  public function setView(template\parser\Elemented $view) {

    $this->view = $view;
  }

  public function parsePath($sPath) {

    return explode('/', $sPath);
  }
}

