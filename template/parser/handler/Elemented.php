<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser;

class Elemented extends Templated implements reflector\elemented {

  const NS = 'http://2013.sylma.org/template';

  protected $aRegistered = array();
  protected $aTemplates = array();
  protected $result;

  protected $logger;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    if ($el->getName() !== 'stylesheet') {

      $this->throwException('Bad root');
    }

    $this->loadTemplates($el);
    $this->loadResult();
  }

  protected function loadLogger() {

    $this->logger = $this->create('logger');
  }
  
  public function lookupNamespace($sPrefix = '') {

    return $this->getNode()->lookupNamespace($sPrefix);
  }

  protected function loadResult() {

    $window = $this->getWindow();

    $result = $window->addVar($window->argToInstance(''));
    $this->result = $result;
  }

  /**
   *
   * @return common\_var
   */
  public function getResult() {

    return $this->result;
  }

  public function addToResult($mContent, $bAdd = true) {

    return $this->getWindow()->addToResult($mContent, $this->getResult(), $bAdd);
  }

  public function register($obj) {

    $this->aRegistered[] = $obj;
  }

  public function getRegistered() {

    return $this->aRegistered;
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'use' : $result = $this->reflectUse($el); break;
      default :

        $result = parent::parseElementSelf($el);
    }

    return $result;
  }

  protected function reflectUse(dom\element $el) {

    if (!$el->hasChildren() || !$el->isComplex()) {

      $this->throwException(sprintf('%s is not valid', $el->asToken()));
    }

    $child = $el->getFirst();
    $parser = $this->getParser($child->getNamespace());
    $tree = $parser->parseRoot($child);

    // This allow use of unknown parser (like action) with generic argument return
    // There are converted to template\tree

    if ($tree instanceof common\_object) {

      $interface = $tree->getInterface();

      if (!$interface->isInstance('\sylma\core\argument')) {

        $this->throwException(sprintf('Parser object of @class %s must be instance of core\\argument', $interface->getName()));
      }

      $tree = $this->create('tree/argument', array($this->getManager(), $tree));
    }

    $this->getManager()->setTree($tree);
  }

  protected function loadLog() {

    $this->getLogger()->asMessage();
  }

  public function getLogger() {

    return $this->logger;
  }
}
