<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser\action\php, \sylma\parser;

require_once(dirname(dirname(__dir__)) . '/Reflector.php');
require_once(dirname(__dir__) . '/compiler.php');

abstract class Action extends parser\Reflector implements parser\action\compiler {

  const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  const CLASS_FILE_DEFAULT = '/sylma/parser/action/cached/document.iml';
  const CLASS_PREFIX = 'class';

  const WINDOW_ARGS = 'classes/php';

  const CALLER_ALIAS = 'caller';
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

    $caller = $this->getControler(self::CALLER_ALIAS);
    $caller->setParent($this);

    $interface = $this->loadInterface($doc);
    $this->setInterface($interface);

    $window = $this->getControler()->create('window', array($this, $controler->getArgument(self::WINDOW_ARGS), $interface->getName()));
    $this->setWindow($window);

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

  protected function loadInterface(dom\handler $doc) {

    $result = null;

    $caller = $this->getControler(self::CALLER_ALIAS);

    if (!$sInterface = $doc->getRoot()->readAttribute('interface', null, false)) {

      $sInterface = self::CLASS_FILE_DEFAULT;
    }
    else {

      $sInterface = $sInterface . '.iml';
    }

    $result = $caller->getInterface($sInterface, $this->getDirectory());

    return $result;
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

  protected function setVariable(dom\element $el, $obj) {

    $result = null;

    if ($sName = $el->readAttribute('set-variable', $this->getNamespace(), false)) {

      $this->aVariables[$sName] = $obj;

      if ($obj instanceof php\_var) {

        $result = $obj;
        $obj->insert();
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
