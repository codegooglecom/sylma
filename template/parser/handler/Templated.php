<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\parser;

abstract class Templated extends reflector\handler\Elemented {

  protected $aTemplates = array();

  public function getCurrentTemplate() {

    if (!$this->aTemplates) {

      $this->launchException('No template defined');
    }

    return end($this->aTemplates);
  }

  public function startTemplate(parser\template $tpl) {

    $this->aTemplates[] = $tpl;
  }

  public function stopTemplate() {

    array_pop($this->aTemplates);
  }

  protected function loadTemplate(dom\element $el) {

    $template = $this->createComponent('component/template', $this);
    $template->parseRoot($el);

    $this->addTemplate($template);
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  protected function addTemplate(parser\component\Template $template) {

    $this->aTemplates[] = $template;
  }

  protected function getTemplate($sPath = '') {

    if ($sPath) {

      $this->throwException('Feature not available');
    }

    //if (!$sMatch) $sMatch = parser_ns\component\Template::MATCH_DEFAULT;

    $result = $this->getDefaultTemplate();

    if (!$result) {

      $this->launchException('No root template found', get_defined_vars());
    }

    return $result;
  }

  protected function getDefaultTemplate() {

    $result = null;

    foreach ($this->aTemplates as $template) {

      if (!$template->getMatch() && !$template->getMode()) {

        $result = $template;
        break;
      }
    }

    return $result;
  }
}
