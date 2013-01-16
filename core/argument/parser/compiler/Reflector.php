<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\php, sylma\storage\fs;

/**
 * Description of Reflector
 *
 * @author Rodolphe Gerber
 */
abstract class Reflector extends parser\reflector\basic\Documented {

  protected function parseElementSelf(dom\element $el) {

    if ($el->isComplex()) {

      $mContent = $this->parseElementComplex($el);
    }
    else {

      $mContent = $this->parseElementSimple($el);
    }

    return $mContent;
  }

  protected function parseElementSimple(dom\element $el) {

    $sValue = $this->loadElementValue($el);
    $mResult = $this->parseElementType($el, $sValue);

    return $mResult;
  }

  protected function parseElementType(dom\element $el, $sValue) {

    $sType = $el->readAttribute('type', null, false);

    switch ($sType) {

      case 'bool' :
      case 'boolean' :

        $mResult = $this->getWindow()->argToInstance((bool) $sValue);

        break;

      case 'array' :

        break;

      case 'string' :
      case 'integer' :
      case '' :

        $mResult = $sValue;

        break;

      default :

        $this->throwException(sprintf('Uknown argument type : %s', $sType));
    }

    return $mResult;
  }

  protected function loadElementValue(dom\element $el) {

    $sValue = $el->read() ? $el->read() : $el->readAttribute('value', null, false);

    return $sValue;
  }

  protected function parseChildrenElement(dom\element $el, &$aResult) {

    $mResult = $this->parseElement($el);

    if (!is_null($mResult)) {

      if ($el->hasChildren() || $this->loadElementValue($el) !== '') {

        $aResult[$el->getName()] = $mResult;
      }
      else {

        $aResult[] = $mResult;
      }
    }
  }

  protected function parseElementForeign(dom\element $el) {

    $result = null;

    if ($el->getNamespace() == $this->getNamespace('arg')) {

      $result = $this->parseElementArgument($el);
    }
    else {

      $result = $this->loadElementForeign($el);
    }

    return $result;
  }

  protected function parseElementArgument(dom\element $el) {

    $result = null;

    switch ($el->getName()) {

      case 'arg' :

        if ($el->getParent()) {

          $this->throwException('Arg element only allowed as root');
        }

      break;
      case 'import' : break;
      //case 'item' : $result = $this->reflectItem($el); break;

      default :

        $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  protected function parseText(dom\text $node) {

    $this->throwException('Mixed element (element and text) or multiple text node not allowed here', array($child->getParent()->asToken()));
  }

  protected function parseElementComplex(dom\element $el) {

    $this->reflectImportsStatic($el);

    $children = $el->getChildren();
    $imports = $this->loadImports($children);

    if ($imports->length) {

      $mResult = $this->reflectImportsDynamic($imports, $this->parseChildren($children));
    }
    else {

      $mResult = $this->parseChildren($children);
    }

    return $mResult;

  }

  protected function loadImports(dom\collection $children, $bStatic = false) {

    $sQuery = $bStatic ? '@static' : 'not(@static)';

    $result = $children->length ? $children->current()->getParent()->queryx("arg:import[$sQuery]", $this->getNS(), false) : $children;

    return $result;
  }

  protected function reflectImportsStatic(dom\element $el) {

    $imports = $this->loadImports($el->getChildren(), true);

    foreach ($imports as $import) $this->reflectImportStatic($el, $import);
  }

  protected function reflectImportStatic(dom\element $parent, dom\element $import) {

    $doc = $this->getDocument($import->read());
    $this->mergeElement($parent, $doc->getRoot(), false);
    $import->replace($doc->getChildren());
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

      if ($child->getNamespace() === $this->getNamespace('arg')) {

        continue;
      }
      else if ($el = $current->getx("{$child->getName()}", array(), false)) {

        $this->mergeElement($el, $child);
      }
    }

    foreach ($current->getChildren() as $child) {

      $import->add($child);
    }

    $current->replace($import);
  }

  protected function mergeElementSimple(dom\element $current, dom\element $import) {

    $import->replace($current);
    $current->remove();
  }

  protected function reflectImportsDynamic(dom\collection $children, $aChildren) {

    $window = $this->getWindow();

    //$self = $window->createVariable('self', $this->getHandlerInstance());
    $closure = $window->create('closure', array($window));

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

    $window = $this->getWindow();

    $sFile = (string) $this->getFile($el->read());
    //$fs = $window->addControler('fs');

    $file = $window->createCall($window->addControler(static::FILE_MANAGER), 'getFile', '\sylma\storage\fs\file', array($sFile));

    $manager = $this->getWindow()->addControler(static::ARGUMENT_MANAGER);
    $result = $this->getWindow()->createCall($manager, 'createArguments', '\sylma\core\argument', array($file));

    return $result;
  }

}

?>
