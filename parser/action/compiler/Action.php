<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser\languages\common, sylma\parser\languages\php, \sylma\parser;

\Sylma::load('/parser/reflector/basic/Documented.php');
\Sylma::load('../reflector.php', __DIR__);

abstract class Action extends parser\reflector\basic\Documented implements parser\action\reflector {

  //const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  const CLASS_FILE_DEFAULT = '/sylma/parser/action/cached/document.iml';
  const CLASS_PREFIX = 'class';

  const CALLER_ALIAS = 'caller';

  private $bTemplate = false;
  private $bString = false;

  protected $aVariables = array();

  /**
   * Interface of new cached class. See @method common\_window::getSelf()
   * @var parser\caller\Domed
   */
  protected $interface;

  protected $return;
  protected $sFormat = 'object';

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

    if ($this->getInterface()->useElement()) {

      $this->setNamespace($this->getInterface()->getNamespace(self::CLASS_PREFIX), self::CLASS_PREFIX, false);
    }
  }

  public function getInterface() {

    return $this->interface;
  }

  public function setInterface(parser\caller $interface) {

    $this->interface = $interface;
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

        $this->throwException(sprintf('Unknown return format in %s', $el->asToken()));

    }

    $this->setFormat($sFormat);
    $this->return = $this->getWindow()->tokenToInstance($sFormat);
  }

  protected function setFormat($sFormat) {

    $this->sFormat = $sFormat;
  }

  protected function getFormat() {

    return $this->sFormat;
  }

  /**
   *
   * @return common\_instance
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

  public function setParent(parser\reflector\documented $parent) {

    return null;
  }

  protected function setVariable(dom\element $el, $obj) {

    $result = null;

    if ($sName = $el->readAttribute('set-variable', $this->getNamespace(), false)) {

      if (array_key_exists($sName, $this->aVariables)) {

        $result = $this->aVariables[$sName];

        if ($obj instanceof common\_var) {

          $obj->insert();
        }

        $result->insert($obj);
      }
      else {

        if ($obj instanceof common\_var) {

          $result = $obj;
          $obj->insert();
        }
        else if ($obj instanceof php\basic\Called) {

          $result = $obj->getVar();
        }
        else {

          $result = $this->getWindow()->addVar($obj);
        }

        $this->aVariables[$sName] = $result;
      }
    }

    return $result;
  }

  public function build(common\_window $window) {

    if ($aResult = $this->parseDocument($this->getDocument())) {

      $window->add($aResult);
    }

    return $window;
  }

  public function asDOM() {

    $window = $this->getWindow();
    $this->build($window);

    $arg = $window->asArgument();

    $result = $arg->asDOM();

    $sTemplate = $this->useTemplate() ? 'true' : 'false';
    $result->getRoot()->setAttribute('use-template', $sTemplate);

    return $result;
  }
}
