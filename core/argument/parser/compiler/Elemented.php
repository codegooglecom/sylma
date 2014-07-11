<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\php, sylma\storage\fs;

class Elemented extends reflector\handler\Elemented implements reflector\elemented {

  const NS = 'http://2013.sylma.org/core/argument';
  const DEFAULT_NS = 'http://2013.sylma.org/core/argument/default';
  const FACTORY_NS = 'http://2013.sylma.org/core/factory';
  const PARSER_MANAGER = 'parser';
  const DEFAULT_PREFIX = 'default';

  //const PREFIX = 'arg';

  protected $allowForeign = true;
  protected $importer;

  public function parseRoot(dom\element $el) {

    $this->registerNamespaces($el);

    if (!$sDefault = self::loadDefaultNamespace($el)) {

      $sDefault = self::DEFAULT_NS;
    }

    $this->setNamespace($sDefault, self::DEFAULT_PREFIX);

    if ($el->getName() !== 'argument') {

      $this->throwException(sprintf('Bad root %s', $el->asToken()));
    }

    $aResult = $this->parseElementComplex($el);

    return $aResult;
  }

  public function parseFromParent(dom\element $el) {

    $aResult = array();
    $this->registerNamespaces($el);
    $this->parseChildrenElementSelf($el, $aResult);

    return $aResult;
  }

  public function parseFromChild(dom\element $el) {

    $aResult = array();
    $this->registerNamespaces($el);
    $this->parseChildrenElementSelf($el, $aResult);

    return $aResult;
  }

  public static function loadDefaultNamespace(dom\element $el) {

    $sNamespace = $el->lookupNamespace();
    return $sNamespace;
  }

  protected function parseElementSelf(dom\element $el) {

    $result = null;

    if ($el->getNamespace() == $this->getNamespace('self')) {

      $result = $this->parseElementArgument($el);
    }
    else {

      if ($el->isComplex()) {

        $result = $this->parseElementComplex($el);
      }
      else {

        $result = $this->parseElementSimple($el);
      }
    }

    return $result;
  }

  protected function parseElementSimple(dom\element $el) {

    $sValue = $el->read();

    if ($sValue !== '') {

      $mResult = $this->parseElementType($el, $sValue);
    }
    else {

      $mResult = $el->getName();
    }

    return $mResult;
  }

  protected function parseElementType(dom\element $el, $sValue) {

    $sType = $this->getType($el);

    switch ($sType) {

      case 'bool' :
      case 'boolean' :

        if (in_array($sValue, array('false'))) {

          $mResult = false;
        }
        else {

          $mResult = (bool) $sValue;
        }

        break;

      case 'int' :
      case 'integer' :

        $mResult = (int) $sValue;
        break;

      case 'numeric' :

        $mResult = $sValue + 0;
        break;

      case 'float' :

        $mResult = (float) $sValue;
        break;

      case 'token' :

        $mResult = array_map('trim', explode(',', $sValue));
        break;

      case '' :

        $mResult = $sValue;
        break;

      default :

        $this->launchException(sprintf('Unknown argument type : %s', $sType), get_defined_vars());
    }

    return $mResult;
  }

  protected function getType(dom\element $el) {

    if (!$sResult = $el->readx('@self:type', $this->getNS(), false)) {

      if ($element = $this->getElement($el)) {

        $sResult = $element->readx('@self:type', array(), false);
      }
    }

    return $sResult;
  }

  /**
   * Return corresponding argument schema element (arg:element)
   *
   * @param $el
   * @return \sylma\dom\element
   */
  protected function getElement(dom\element $el) {

    return !$el->isRoot() ? $el->getParent()->getx("self:element[@name='{$el->getName()}']", array(), false) : null;
  }

  protected function listTokenAttribute(dom\element $el) {

    $sAttribute = $sExtend = '';

    if ($element = $this->getElement($el)) {

      $sAttribute = $element->readx('@key', array(), false);
      $sExtend = $element->readx('@extend', array(), false);
    }

    return array($sAttribute, $sExtend);
  }

  protected function parseChildrenElementSelf(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementSelf($el);

    if (!is_null($mResult)) {

      list($sAttribute, $sExtend) = $this->listTokenAttribute($el);

      if ($sAttribute) {

        $sAttribute = $el->readx($sAttribute);

        if ($sExtend) {

          $sExtend = $el->readx($sExtend);

          if (!isset($aResult[$sExtend])) {

            $aResult[$sExtend] = array();
          }
          else if (!is_array($aResult[$sExtend])) {

            $this->launchException('Cannot extend a key already used by simple value');
          }

          $aResult[$sExtend][$sAttribute] = $mResult;
        }
        else {

          $aResult[$sAttribute] = $mResult;
        }
      }
      else if ($el->hasChildren() || $el->read() !== '') {

        $aResult[$el->getName()] = $mResult;
      }
      else {

        $aResult[] = $mResult;
      }
    }
  }

  protected function parseChildrenElementForeign(dom\element $el, array &$aResult) {

    if ($el->getNamespace() !== self::FACTORY_NS) {

      $this->throwException(sprintf('Bad child %s only factory allowed', $el->asToken()));
    }

    $mResult = $this->parseElementForeign($el);

    if (!is_null($mResult)) {

      $aResult = array_merge($aResult, $mResult);
    }
  }

  protected function parseElementArgument(dom\element $el) {

    $result = null;

    switch ($el->getName()) {

      case 'import' : break;
      case 'element' :  break;
      //case 'item' : $result = $this->reflectItem($el); break;

      default :

        $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  protected function parseText(dom\text $node, $bTrim = true) {

    $this->throwException('Mixed element (element and text) or multiple text node not allowed here', array($node->getParent()->asToken()));
  }

  protected function parseElementComplex(dom\element $el) {

    $this->reflectImportsStatic($el);

    $children = $el->getChildren();
    $imports = $this->loadImports($children);

    if ($imports && $imports->length) {

      $mResult = $this->reflectImportsDynamic($imports, $this->parseChildren($children));
    }
    else {

      $mResult = $this->parseChildren($children);
    }

    return $mResult;

  }

  protected function loadImports(dom\collection $children, $bStatic = false) {

    $result = null;
    $sQuery = $bStatic ? '@static' : 'not(@static)';

    if ($children->length && $children->current()) {

      $parent = $children->current()->getParent();

      if ($parent->getType() == $parent::ELEMENT) {

        $result = $parent->queryx("self:import[$sQuery]", $this->getNS(), false);
      }
    }

    return $result;
  }

  protected function reflectImportsStatic(dom\element $el) {

    $imports = $this->loadImports($el->getChildren(), true);

    if ($imports) {

      foreach ($imports as $import) {

        $this->reflectImportStatic($el, $import);
      }
    }
  }

  protected function reflectImportStatic(dom\element $parent, dom\element $import) {

    $doc = $this->getSourceFile($import->read())->getDocument();

    $this->mergeElement($parent, $doc->getRoot(), false);
    $import->replace($doc->getChildren());

    $sNamespace = static::loadDefaultNamespace($doc->getRoot());

    if (!$this->useNamespace($sNamespace)) $this->setUsedNamespace($sNamespace);
  }

  protected function mergeElement(dom\element $current, dom\element $import, $bCheckNS = true) {

    if ($bCheckNS && $current->getNamespace() !== $import->getNamespace()) {

      $this->throwException(sprintf('Cannot merge elements with same name but different namespaces %s and %s', $current->asToken(), $import->asToken()));
    }

    if ($current->isComplex()) {

      if (!$import->isComplex()) {

        $this->throwException(sprintf('Cannot merge simple type %s on complex type %s', $import->asToken(), $current->asToken()));
      }

      $this->mergeElementComplex($current, $import);
    }
    else {

      if ($import->isComplex()) {

        $this->throwException(sprintf('Cannot merge complex type %s on simple type %s', $import->asToken(), $current->asToken()));
      }

      $this->mergeElementSimple($current, $import);
    }
  }

  protected function mergeElementComplex(dom\element $current, dom\element $import) {

    foreach ($import->getChildren() as $child) {

      if ($child->getNamespace() === $this->getNamespace('self')) {

        continue;
      }
      else if ($el = $current->getx("{$child->getName()}", array(), false)) {

        $this->mergeElement($el, $child);
      }
    }

    foreach ($current->getChildren() as $child) {

      $import->add($child);
    }

    return $current->replace($import);
  }

  protected function mergeElementSimple(dom\element $current, dom\element $import) {

    $result = $import->replace($current);
    $current->remove();

    return $result;
  }

  protected function reflectImportsDynamic(dom\collection $children, $aChildren) {

    $window = $this->getWindow();

    //$self = $window->createVariable('self', $this->getHandlerInstance());
    $closure = $window->createClosure();

    $bChildren = false;
    $window->setScope($closure);
    $import = $children->current();

    $handler = $this->reflectImportDynamic($import);

    if ($children->length > 1 || $aChildren) {

      $bChildren = true;
      $children->next();

      while ($children->current()) {

        $import = $children->current();
        $this->mergeArguments($handler->getVar(), $this->reflectImportDynamic($import));

        $children->next();
      }

      if ($aChildren) {

        $array = $window->argToInstance($aChildren);
        $this->mergeArguments($handler->getVar(), $array);
      }

      $closure->addContent($handler->getVar());
    }

    if ($import->getParent()->isRoot()) {

      $call = $window->createCall($handler->getVar(), 'asArray', 'php-array');
      $closure->addContent($call);
    }
    else if (!$bChildren) {

      $closure->addContent($handler);
    }

    $window->stopScope();

    return $closure;
  }

  protected function mergeArguments(common\_var $first, common\argumentable $second) {

    $window = $this->getWindow();
    $call = $window->createCall($first, 'merge', $window->tokenToInstance('\sylma\core\argument'), array($second));

    $window->add($call);
  }

  protected function reflectImportDynamic(dom\element $el) {

    return $this->parseComponent($el);
  }

  protected function loadImporter() {

    $window = $this->getWindow();
    $instance = $window->tokenToInstance($this->read('importer'));
    $result = $window->create('class', array($window, $instance->getInterface()));

    $this->importer = $result;
  }

  public function getImporter() {

    if (!$this->importer) {

      $this->loadImporter();
    }

    return $this->importer;
  }
}

