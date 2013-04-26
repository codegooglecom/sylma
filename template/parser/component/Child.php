<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser as parser_ns;

class Child extends reflector\component\Foreigner {

  protected $template;

  protected function getTemplate($sPath = '') {

    if (!$this->template) {

      $this->launchException('No template defined');
    }

    return $this->template;
  }

  protected function parseComponent(dom\element $el) {

    if ($this->allowComponent()) {

      $result = parent::parseComponent($el);
    }
    else {

      $result = $this->getTemplate()->parseComponent($el, $this->getParser());
    }

    return $result;
  }

  public function setTemplate(parser_ns\template $template) {

    /*
    if (!$template->isCloned()) {

      $this->launchException('Template must be cloned');
    }
    */

    $this->template = $template;
  }

  protected function getResult() {

    return $this->getTemplate()->getResult();
  }

  protected function getTree() {

    return $this->getTemplate()->getTree();
  }

  protected function startLog($sMessage = '', array $aVars = array()) {

    $this->getParser()->getLogger()->startComponent($this, $sMessage, array_merge(array(
      'node' => $this->getNode()
    ), $aVars));
  }

  protected function stopLog() {

    $this->getParser()->getLogger()->stopComponent();
  }
}

