<?php

namespace sylma\storage\xml\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\core\functions\path;

class Settings extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  const ARGUMENT_NS = 'http://2013.sylma.org/core/argument';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $manager = $this->getManager(self::PARSER_MANAGER);

    if ($el->countChildren() > 1) {

      $this->launchException('Invalid content, one child expected');
    }

    $doc = $this->createDocument();
    $doc->addElement('arg:argument', $el->getFirst(), array(), self::ARGUMENT_NS);

    $builder = $manager->loadBuilder($this->getSourceFile(), $this->getSourceDirectory(), null, $doc);
    $args = $builder->buildStatic();

    $this->getParser()->setSettings($args);
  }

  public function asArray() {

    return array();
  }

}

