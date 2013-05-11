<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template as template_ns;

class _If extends Unknowned implements common\arrayable, template_ns\parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    //$this->allowForeign(true);
    //$this->allowUnknown(true);
    $this->allowText(true);
  }

  public function asArray() {

    $aResult = array();
    $aContent = $aTokens = array();

    $aChildren = $this->parseChildren($this->getNode()->getChildren());

    foreach ($aChildren as $val) {

      if ($val instanceof Token) {

        $aTokens[] = $val;
      }
      else {

        $aContent[] = $val;
      }
    }

    $window = $this->getWindow();
    $if = $this->getTemplate()->getPather()->parseExpression($this->readx('@test'));
    $content = $this->getParser()->addToResult($aContent, false);

    if ($aTokens) {

      if ($content) {

        //$this->launchException('Not yet tested');
        $test = $window->createVariable('', 'php-boolean');
        $assign = $window->createAssign($test, true);
        $if->addContent($assign);

        $aResult = array($window->createCondition($test, $content));
      }

      $el = $this->getParser()->getElement();

      foreach ($aTokens as $token) {

        $var = $window->createVariable('', 'php-string');
        $assign = $window->createAssign($var, $window->toString($token->asValue()));

        $if->addContent($assign);
        $if->addElse($window->createAssign($var, $window->argToInstance('')));

        $el->addToken($token->getName(), $var);
        //$var->insert();
        $window->add($if);
      }
    }
    else {

      $if->addContent($content);
      $aResult = array($if);
    }

    return $aResult;
  }
}

