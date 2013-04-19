<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\parser\reflector, sylma\template\parser;

abstract class Templated extends reflector\handler\Elemented {

  protected function loadTemplates() {

    $el = $this->getNode();

    foreach ($el->queryx('self:template', $this->getNS()) as $child) {

      $template = $this->createComponent('component/template', $this);
      $template->parseRoot($child);

      $this->addTemplate($template);
    }
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

      if (!$template->getMatch()) {

        $result = $template;
        break;
      }
    }

    return $result;
  }
}
