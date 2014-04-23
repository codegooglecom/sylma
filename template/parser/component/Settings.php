<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\template, sylma\parser\languages\common;

class Settings extends Child implements common\arrayable, template\parser\component {

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

    $this->setSettings($args, false);
  }

  protected function build() {

    if (!$target = $this->getTree(false)) {

      $target = $this->getHandler()->getResource();
    }

    $target->setSettings($this->getSettings());
  }

  public function asArray() {

    $this->build();

    return array();
  }

}

