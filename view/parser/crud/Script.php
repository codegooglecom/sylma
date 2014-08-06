<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\action, sylma\template;

class Script extends action\component\Script implements template\parser\component {

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->setUsedNamespace($this->getNamespace(), self::PREFIX, false);
  }

  public function setTemplate(template\parser\template $template) {

    $this->template = $template;
  }

  protected function parseElementSelf(dom\element $el) {

    return $this->template->parseElementSelf($el);
  }

  protected function loadPath($sPath) {

    $root = $this->getRoot();
    $path = $root->getPath($sPath);

    return parent::loadPath($path->asPath());
  }
}

