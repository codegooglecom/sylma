<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser;

abstract class Action extends parser\reflector\basic\Documented {

  //const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  //const CLASS_DEFAULT = '\sylma\parser\action\cached';

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

  public function __construct(parser\action\Manager $manager, dom\handler $doc, fs\directory $dir) {

    $this->setDocument($doc);
    $this->setManager($manager);
    $this->setNamespace($manager->getNamespace(), 'self');
    $this->setDirectory($dir);
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

  public function getLastElement() {

    return $this->lastElement;
  }

  public function setLastElement($lastElement) {

    $this->lastElement = $lastElement;
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

  protected function getVariable($sName) {

    if (!array_key_exists($sName, $this->aVariables)) {

      $this->throwException(sprintf('Variable %s does not exists', $sName));
    }

    return $this->aVariables[$sName];
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

  /**
   * TODO : make public for sub-action integration
   *
   * @param common\_window $window
   * @return common\_window
   */
  protected function build() {

    if ($aResult = $this->parseDocument($this->getDocument())) {

      $this->getWindow()->add($aResult);
    }
  }

  public function asDOM() {

    $this->build();
    $arg = $this->getWindow()->asArgument();

    $result = $arg->asDOM();

    $sTemplate = $this->useTemplate() ? 'true' : 'false';
    $result->getRoot()->setAttribute('use-template', $sTemplate);

    return $result;
  }
}
