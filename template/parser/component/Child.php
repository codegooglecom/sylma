<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class Child extends reflector\component\Foreigner {

  protected $template;

  /**
   * @return template\parser\template
   */
  public function getTemplate($sPath = '', $bDebug = true) {

    if (!$this->template && $bDebug) {

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

  public function setTemplate(template\parser\template $template) {

    $this->template = $template;
  }

  protected function getResult() {

    return $this->getTemplate()->getResult();
  }

  protected function getTree($bDebug = true) {

    $result = null;

    if ($template = $this->getTemplate('', $bDebug)) {

      $result = $template->getTree($bDebug);
    }

    return $result;
  }

  protected function parseText(dom\text $node, $bTrim = true) {

    return $this->getParser()->xmlize(parent::parseText($node, $bTrim));
  }

  /**
   * Set handler with self::setHandler()
   * @todo completely replace
   */
  protected function setParser(reflector\domed $parent) {

    parent::setParser($parent);
    $this->setHandler($parent);
  }

  protected function setHandler(template\parser\handler $val) {

    $this->handler = $val;
  }

  /**
   * @return template\parser\handler
   */
  protected function getHandler() {

    return $this->handler;
  }
}

