<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser, sylma\parser\languages\php;

class Variable extends Child implements common\arrayable, parser\component {

  protected $sName;
  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();

    $this->allowForeign(true);
    $this->allowText(true);
  }

  protected function loadName() {

    $this->sName = $this->readx('@name');
  }

  public function getName() {

    return $this->sName;
  }

  protected function build() {

    $this->getTemplate()->setVariable($this);

    $aContent = $this->parseComponentRoot($this->getNode());

    return $this->loadVar($aContent);
  }

  protected function extractContent($mContent) {

    $mContent = $this->getWindow()->parseArrayables(array($mContent));
    return is_array($mContent) && count($mContent) == 1 ? current($mContent) : $mContent;
  }

  protected function loadVar($mContent) {

    $aResult = array();
    //$mContent = $this->extractContent($mContent);
    $mContent = $this->getWindow()->parseArrayables(array($mContent));

    if ($mContent instanceof common\_var) {

      self::setContent($mContent);
    }
    else {

      if (!$mContent) {

        $mContent = '';
      }

      $window = $this->getWindow();

      switch ($this->readx('@format', false)) {

        case 'array' :

          self::setContent($window->createVar($this->getWindow()->argToInstance($mContent)));
          $aResult[] = $this->getContent()->getInsert();
          break;

        default :

          self::setContent($this->getWindow()->createVar($this->getWindow()->argToInstance('')));
          $aResult[] = $this->getWindow()->toString($mContent, $this->getContent(), false, true);
      }
    }

    return $aResult;
  }

  protected function setContent($var) {

    $this->var = $var;
  }

  public function getContent() {

    if (is_null($this->var)) {

      $this->launchException('Variable not ready');
    }

    return $this->var;
  }

  public function asArray() {

    return array($this->build());
  }
}

