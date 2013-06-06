<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Context extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);
  }

  protected function parseChildren(dom\collection $children) {

    if ($aContent = parent::parseChildren($children)) {

      $aResult = $this->getWindow()->parseArrayables($aContent);
      //$aResult = count($aResult) > 1 ? $aResult : current($aResult);
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }

  protected function loadCalls(common\callable $context, $aContent) {

    foreach ($aContent as $content) {

      $aResult[] = $context->call('add', array($content));
    }

    return $aResult;
  }

  public function asArray() {

    $window = $this->getWindow();
    $sContext = $this->readx('@name');

    $contexts = $window->getVariable('contexts');
    $content = $this->parseChildren($this->getNode()->getChildren());

    if ($this->readx('@required')) {

      $result = $this->loadCalls($contexts->call('get', array($sContext), '\sylma\core\argument'), $content);
    }
    else {

      $var = $window->createVar($contexts->call('get', array($sContext, false), '\sylma\core\argument'));
      $result = $window->createCondition($var->getInsert(null, false), $this->loadCalls($var, $content));
    }

    return array($result);
  }
}
