<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

\Sylma::load('/parser/reflector/basic/Documented.php');

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

    $imports = $children->current()->getParent()->queryx('arg:import', $this->getNS(), false);

    if ($imports->length) {

      $aChildren = parent::parseChildren($children);

      $window = $this->getWindow();
      $closure = $window->create('closure', array($window));
      $window->setScope($closure);

      $import = $this->reflectImport($imports->current());

      if ($imports->length == 1 && !$aChildren) {

        $closure->addContent($import);
      }
      else {

        $closure->addContent($import->getVar());

        $imports->next();

        while ($import = $imports->current()) {

          $this->mergeArguments($closure, $this->reflectImport($import));
          $imports->next();
        }

        if ($aChildren) {

          $array = $window->argToInstance($aChildren);
          $this->mergeArguments($closure, $array);
        }
      }

      $window->stopScope();

      $mResult = $closure;
    }
    else {

      $mResult = parent::parseChildren($children);
    }

    return $mResult;
  }

  protected function mergeArguments(php\basic\_Closure $closure, common\_instance $arg) {

    $return = $closure->getReturn();

    $merge = $this->getWindow()->createCall($return, 'merge', $window->stringToInstance('\sylma\core\argument'), array($arg->getVar()));
    $closure->addContent($merge);

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

  protected function reflectImport(dom\element $el) {

    $window = $this->getWindow();

    $result = $window->createFunction('include', $window->stringToInstance('\sylma\core\argument'), array($el->read()));

    return $result;
  }

}

?>
