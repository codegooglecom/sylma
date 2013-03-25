<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\parser as parser_ns, sylma\parser\languages\common;

class Elemented extends reflector\handler\Elemented implements reflector\elemented {

  const NS = 'http://2013.sylma.org/template';

  protected $aRegistered = array();
  protected $aTemplates = array();
  protected $result;

  protected static $aParsed = array();

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    if ($el->getName() !== 'stylesheet') {

      $this->throwException('Bad root');
    }

    $this->loadTemplates($el);
    $this->loadResult();

    //$content = $this->parseChildren($el->getChildren());
    //$this->setContent($content);
    //parent::parseRoot($el->getFirst());
  }

  public function lookupNamespace($sPrefix = '') {

    return $this->getNode()->lookupNamespace($sPrefix);
  }

  protected function loadResult() {

    $window = $this->getWindow();

    $result = $window->addVar($window->argToInstance(''));
    $this->result = $result;
  }

  protected function loadTemplates() {

    $el = $this->getNode();

    foreach ($el->queryx('self:template', $this->getNS()) as $child) {

      $template = $this->createComponent('component/template', $this);
      $template->parseRoot($child);

      $this->addTemplate($template);
    }
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  protected function addTemplate(parser_ns\component\Template $template) {

    $this->aTemplates[] = $template;
  }

  protected function getTemplate($sPath = '') {

    if ($sPath) {

      $this->throwException('Feature not available');
    }

    //if (!$sMatch) $sMatch = parser_ns\component\Template::MATCH_DEFAULT;

    $result = $this->getDefaultTemplate();

    if (!$result) {

      $this->launchException('No root template found', get_defined_vars());
    }

    return $result;
  }

  protected function getDefaultTemplate() {

    $result = null;

    foreach ($this->aTemplates as $template) {

      if (!$template->getMatch()) {

        $result = $template;
        break;
      }
    }

    return $result;
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

  public function getContent() {

    return $this->content;
  }

  public function setContent($content) {

    $this->content = $content;
  }

  protected function parseArrayables(array $aContent) {

    return $this->getWindow()->parseArrayables($aContent);
  }

  public function toString($mContent) {

    if (is_array($mContent)) {

      $aContent = $this->parseArrayables($mContent);
      $aResult = array();

      foreach ($aContent as $mVal) {

        if ($mVal instanceof common\structure) {

          $this->addToResult($aResult);
          $this->getWindow()->add($mVal);

          $aResult = array();
        }
        else {

          $aResult[] = $mVal;
        }
      }

      if ($aResult) $result = $this->getWindow()->createString($aResult);
      else $result = null;
    }
    else if ($mContent instanceof common\argumentable) {

      $result = $mContent->asArgument();
    }
    else {

      $this->throwException(sprintf('Cannot add %s to result', $this->show($mContent)));
    }

    return $result;
  }

  public function getResult() {

    return $this->result;
  }

  public function addToResult($mContent, $bAdd = true) {

    $window = $this->getWindow();

    $content = $this->toString(array($mContent));

    if ($content) {

      $result = $window->createAssign($this->getResult(), $content, '.');
      if ($bAdd) $window->add($result);
    }
    else {

      $result = null;
    }

    return $result;
  }
}
