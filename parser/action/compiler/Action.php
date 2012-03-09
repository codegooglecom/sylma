<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser\action\php, \sylma\parser;

require_once(dirname(dirname(__dir__)) . '/Reflector.php');
require_once(dirname(__dir__) . '/compiler.php');

abstract class Action extends parser\Reflector implements parser\action\compiler {

  const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  const CLASS_DEFAULT = '\sylma\parser\action\cached\Document';
  const CLASS_PREFIX = 'class';

  const WINDOW_ARGS = 'classes/php';

  /**
   * See @method setFile()
   * @var storage\fs
   */
  private $document;

  private $bTemplate = false;
  private $bString = false;

  protected $aVariables = array();

  /**
   * Sub parsers
   * @var array
   */
  private $aParsers = array();

  /**
   * Interface of new cached class. See @method php\_window::getSelf()
   * @var parser\caller\Domed
   */
  protected $interface;

  protected $return;

  // controler : getNamespace, create, getArgument

  public function __construct(parser\action\Controler $controler, dom\handler $doc, fs\directory $dir) {

    $this->setDocument($doc);
    $this->setControler($controler);
    $this->setNamespace($controler->getNamespace(), 'self');
    $this->setDirectory($dir);

    $sClass = $this->loadClass($doc);

    $window = $this->getControler()->create('window', array($this, $controler->getArgument(self::WINDOW_ARGS), $sClass));
    $this->setWindow($window);

    $caller = $this->getControler('caller');
    $caller->setParent($this);

    $this->setInterface($caller->getInterface($sClass));

    $security = $this->getControler()->create('parser/security');
    $this->setParser($security, $security->getNS());

    $this->setNamespace($this->getInterface()->getNamespace(self::CLASS_PREFIX), self::CLASS_PREFIX, false);
  }

  protected function setDocument(dom\handler $doc) {

    $this->document = $doc;
  }

  protected function getDocument() {

    return $this->document;
  }

  public function getInterface() {

    return $this->interface;
  }

  public function setInterface(parser\caller $interface) {

    $this->interface = $interface;
  }

  protected function getParser($sUri) {

    $parser = null;

    if (array_key_exists($sUri, $this->aParsers)) {

      $parser = $this->aParsers[$sUri];
      $parser->setParent($this);
    }

    return $parser;
  }

  protected function setParser(parser\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }

  protected function loadClass(dom\handler $doc) {

    if (!$sResult = $doc->getRoot()->readAttribute('class', null, false)) {

      $sResult = self::CLASS_DEFAULT;
    }

    return $sResult;
  }

  protected function setReturn(dom\element $el) {

    $sFormat = $el->readAttribute('format');

    switch ($sFormat) {

      case 'dom' :
      case 'txt' :

        $this->useString(true);

      break;

      case 'array' :
      case 'object' :

        $this->useString(false);

      break;

      default :

        $this->throwException(txt('Unknown return format in %s', $el->asToken()));

    }

    $this->return = $this->getWindow()->stringToInstance($sFormat);
  }

  /**
   *
   * @return php\_instance
   */
  protected function getReturn() {

    return $this->return;
  }

  public function useTemplate($bValue = null) {

    if (!is_null($bValue)) {

      $this->bTemplate = $bValue;
      $this->useString(true);
    }

    return $this->bTemplate;
  }

  public function useString($bValue = null) {

    if (!is_null($bValue)) $this->bString = $bValue;

    return $this->bString;
  }

  public function setParent(parser\elemented $parent) {

    return null;
  }

  /**
   *
   * @param php\_var $var
   * @param dom\collection $children
   * @return array|\sylma\parser\action\php\_var
   */
  protected function runVar(php\_var $var, dom\collection $children) {

    $aResult = array();

    if ($children->current()) {

      $window = $this->getWindow();
      $window->setScope($var);

      $caller = $this->getControler('caller');
      $interface = $caller->loadObject($var);

      while ($child = $children->current()) {

        $children->next();
        $call = $interface->parseCall($child, $var);

        if ($sub = $this->setVariable($child, $call)) {

          $aResult[] = $sub;
        }
        else {

          $aResult[] = $call;
        }
      }

//      if (count($aResult) == 1) $mResult = $aResult[0];
//      else $mResult = $aResult;

      $window->stopScope();
    }

    return $aResult;
  }

  /**
   *
   * @param php\basic\CallMethod $call
   * @param dom\collection $children
   * @return array
   */
  public function runCalls(php\basic\CallMethod $call, dom\collection $children) {

    $aResult = array();

    if ($children->current()) {

      $var = $call->getVar();
      $aResult = $this->runVar($var, $children);
    }

    return $aResult;
  }

  /**
   *
   * @param php\basic\CallMethod $call
   * @param dom\collection $children
   * @return array
   */
  public function runConditions(php\basic\CallMethod $call, dom\collection $children) {

    $aResult = array();

    while ($child = $children->current()) {

      if ($child->getNamespace() == $this->getNamespace()) {

        // from here, condition can be builded

        $sName = $child->getName();
        $window = $this->getWindow();

        if ($child->getChildren()->length != 1) {

          $this->throwException(txt('Invalid children, one child expected in %s', $child->asToken()));
        }

        $content = $this->parse($child->getFirst());
        $var = $window->createVar($content);

        $window->add($window->create('assign', array($window, $var, $window->stringToInstance('php-null'))));
        $assign = $window->create('assign', array($window, $var, $content));

        if ($sName == 'if') {

          $condition = $window->create('condition', array($window, $call->getVar(), $assign));
        }
        else if ($sName == 'if-not') {

          $not = $window->createNot($call->getVar());
          $condition = $window->create('condition', array($window, $not, $assign));
        }
        else {

          $this->throwException(txt('Condition expected, invalid %s', $child->asToken()));
        }

        $window->add($condition);
        $aResult[] = $var;
      }
      else {

        break;
      }

      $children->next();
    }

    return $aResult;
  }

  protected function setVariable(dom\element $el, $obj) {

    $result = null;

    if ($sName = $el->readAttribute('set-variable', $this->getNamespace(), false)) {

      $this->aVariables[$sName] = $obj;

      if ($obj instanceof php\_var) {

        $result = $obj;
      }
      else if ($obj instanceof php\basic\Called) {

        $result = $obj->getVar();
      }
      else {

        $result = $this->getWindow()->addVar($obj);
      }
    }

    return $result;
  }

  public function asDOM() {

    $doc = $this->getDocument();
    $window = $this->getWindow();

    if ($aResult = $this->parseDocument($doc)) {

      $window->add($aResult);
    }
    //dspf($aResult[1]->asArgument());
    //dspf($aResult);

    $arg = $window->asArgument();

    //$tst = $arg->get('window')->query();
    //dspm((string) $tst[1]);

    $result = $arg->asDOM();

    $sTemplate = $this->useTemplate() ? 'true' : 'false';
    $result->getRoot()->setAttribute('use-template', $sTemplate);

    return $result;
  }
}
