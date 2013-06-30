<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql\template;

class Token extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $bBuilded = false;

  public function parseRoot(dom\element $el) {

    $this->allowForeign(true);
    $this->allowText(true);

    $this->setNode($el);
  }

  protected function build() {

    return $this->buildForm($this->getParser()->getTree());
  }

  protected function buildForm(template\component\Rooted $table) {

    $token = $table->getToken();
    $path = $this->parseComponentRoot($this->getNode());

    return array(
      $token->getInsert(),
      $token->call('savePath', array($this->getWindow()->createString($path)))->getInsert(),
    );
  }

  public function asArray() {

    return array($this->build());
  }
}

