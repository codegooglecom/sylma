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

      $mContent = $this->parseChildren($el->getChildren());
    }
    else {

      $mContent = $el->read();
    }

    return $mContent;
  }

  protected function parseChildrenElement(dom\element $el, &$aResult) {

    $mResult = $this->parseElement($el);
    if (!is_null($mResult)) $aResult[$el->getName()] = $mResult;
  }

  protected function parseElementForeign(dom\element $el) {

    $result = null;

    if ($el->getNamespace() == $this->getNamespace('arg')) {

      $result = $this->parseElementArgument($el);
    }
    else if ($parser = $this->loadParser($el->getNamespace())) {

      $result = $parser->parseRoot($el);
    }
    else {

      $this->throwException('No foreign element allowed with this parser');
    }

    return $result;
  }

  protected function parseText(dom\text $node) {

    $this->throwException('Mixed element (element and text) or multiple text node not allowed here', array($child->getParent()->asToken()));
  }

  protected function parseChildren(dom\collection $children) {

    $imports = $children->length ? $children->current()->getParent()->queryx('arg:import', $this->getNS(), false) : $children;

    if ($imports->length) {

      $mResult = $this->reflectImports($imports, parent::parseChildren($children));
    }
    else {

      $mResult = parent::parseChildren($children);
    }

    return $mResult;
  }

  protected function reflectImports(dom\collection $children, $aChildren) {

    $window = $this->getWindow();

    $self = $window->createVariable('self', $this->getHandlerInstance());
    $closure = $window->create('closure', array($window, array($self)));

    $window->setScope($closure);
    $import = $children->current();

    $handler = $this->reflectImport($import);

    if ($children->length != 1 || $aChildren) {

      //$var = $handler->getVar();
      //$closure->addContent($var);

      $children->next();

      while ($children->current()) {

        $import = $children->current();

        $this->mergeArguments($handler, $this->reflectImport($import));

        $children->next();
      }

      if ($aChildren) {

        $array = $window->argToInstance($aChildren);
        $this->mergeArguments($handler, $array);
      }
    }

    if ($import->getParent()->isRoot()) {

      $call = $window->createCall($handler->getVar(), 'asArray', 'php-array');
      $closure->addContent($call);
    }
    else {

      $closure->addContent($handler);
    }

    $window->stopScope();

    return $closure;
  }

  protected function mergeArguments($first, common\_instance $second) {

    $window = $this->getWindow();
    $call = $window->createCall($first->getVar(), 'merge', $window->tokenToInstance('\sylma\core\argument'), array($second));

    $window->add($call);
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

  protected function getHandlerInstance() {

    $sClass = $this->getControler()->getClassName('handler');
    $result = $this->getWindow()->tokenToInstance($sClass);

    return $result;
  }

  protected function reflectImport(dom\element $el) {

    $window = $this->getWindow();

    $sFile = (string) $this->getFile($el->read());
    //$fs = $window->addControler('fs');

    $file = $window->createCall($window->getScope()->getVariable('self'), 'getFile', '\sylma\storage\fs\file', array($sFile));
    $result = $window->createInstanciate($this->getHandlerInstance(), array($file));

    return $result;
  }

}

?>
