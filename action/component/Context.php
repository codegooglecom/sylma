<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Context extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);
  }

  public function asArray() {

    $window = $this->getWindow();
    $sContext = $this->readx('@name');
    $var = $window->createVar($window->getVariable('contexts')->call('get', array($sContext, false), '\sylma\core\argument'));
    $content = $var->call('add', array($this->parseChildren($this->getNode()->getChildren())));
    $result = $window->createCondition($var->getInsert(null, false), $content);

    return array($result);
  }

  protected function parseChildren(dom\collection $children) {

    if ($aContent = parent::parseChildren($children)) {

      $aResult = $this->getWindow()->parseArrayables($aContent);
      $aResult = count($aResult) > 1 ? $aResult : current($aResult);
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }
}
