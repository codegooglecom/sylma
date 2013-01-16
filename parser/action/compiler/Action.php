<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser;

abstract class Action extends parser\reflector\basic\Documented {

  //const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  //const CLASS_DEFAULT = '\sylma\parser\action\cached';

  private $bTemplate = false;
  private $bString = false;

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
