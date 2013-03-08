<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Handler extends sql\schema\Handler {

  protected $var;
  protected $query;
  protected $template;
  protected $view;

  protected $aTemplates = array();

  public function getView() {

    return $this->view;
  }

  public function setView(template\parser\Elemented $view) {

    $this->view = $view;
  }

  public function lookupTemplate(schema\parser\element $element, $sContext, $sMode) {

    $iLast = 0;
    $result = null;

    foreach ($this->getTemplates() as $template) {

      $iWeight = $template->getWeight($element, $sContext, $sMode);
      if ($iWeight && $iWeight >= $iLast) {

        $result = $template;
        $iLast = $iWeight;
      }
    }

    return $result;
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  public function loadTemplates(array $aTemplates = array()) {

    $this->aTemplates = $aTemplates;
  }

  public function lookupNamespace($sPrefix = 'target', dom\element $context = null) {

    if (!$sNamespace = parent::lookupNamespace($sPrefix, $context)) {

      $sNamespace = $this->getView()->lookupNamespace($sPrefix);
    }

    return $sNamespace;
  }

  public function parsePath($sPath) {

    return explode('/', $sPath);
  }
}

