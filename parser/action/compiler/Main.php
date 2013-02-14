<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser\reflector, sylma\parser\action;

abstract class Main extends reflector\basic\Foreigner {

  const FORMATER_ALIAS = 'formater';

  protected $parent;
  protected $allowComponent = false;
  protected $aParsers = array();

  /**
   *
   * @var common\_window
   */
  private $window;
  protected $sourceDir;

  private $bTemplate = false;
  private $bString = false;

  protected $return;
  protected $sFormat = 'object';



  // controler : getNamespace, create, getArgument

  public function __construct(action\Manager $manager, dom\handler $doc, fs\directory $dir) {

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

  public function setParent(reflector\domed $parent) {

    return null;
  }

  /**
   * @param common\_window $window
   */
  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return common\_window
   */
  public function getWindow() {

    if (!$this->window) {

      $this->throwException('No window defined');
    }

    return $this->window;
  }

  /**
   *
   * @param $doc
   * @return array
   */
  protected function parseDocument(dom\document $doc) {

    return $this->parseChildren($doc->getChildren());
  }

  /**
   *
   * @return array
   */
  protected function build() {

    if ($aResult = $this->parseDocument($this->getDocument())) {

      $this->getWindow()->add($aResult);
    }
  }

  protected function buildInstanciation(common\_window $window, array $aArguments) {

    $new = $window->createInstanciate($window->getSelf()->getInstance(), $aArguments);
    //$window->add($new);
    $window->setReturn($new);
  }

  public function getSourceDirectory() {

    return parent::getSourceDirectory();
  }

  public function useNamespace($sNamespace, $bParent = false) {

    return parent::useNamespace($sNamespace, $bParent);
  }

  public function parse(dom\node $node) {

    return parent::parse($node);
  }

  /**
   *
   * @return \sylma\parser\reflector\documented
   */
  public function getParentParser() {

    $result = null;
    $parent = $this->getParent();

    if ($parent) {

      $result = $parent->getParentParser();

      if (!$result) {

        $result = $parent;
      }
    }

    return $result;
  }

  protected function lookupParserForeign($sNamespace) {

    if ($this->getParentParser()) {

      return $this->getParentParser()->lookupParser($sNamespace);
    }
    else {

      $result = $this->getParser($sNamespace);
    }

    return $result;
  }

  /**
   * Exception free parser loader
   *
   * @param string $sNamespace
   * @return parser\domed
   */
  protected function getParser($sNamespace) {

    if (array_key_exists($sNamespace, $this->aParsers)) {

      $result = $this->aParsers[$sNamespace];
    }
    else {

      $result = $this->createParser($sNamespace);

      if ($result) {

        $this->setParser($result, $result->getUsedNamespaces());
      }
    }

    //if ($result) $result->setParent($this);

    return $result;
  }

  protected function createParser($sNamespace) {

    $manager = $this->getManager('parser');
    return $manager->getParser($sNamespace, $this, null, false);
  }

  public function lookupParser($sNamespace) {

    $result = null;

    if ($this->useNamespace($sNamespace)) {

      $result = $this;
    }
    else {

      $result = $this->getParser($sNamespace);
    }

    return $result;
  }

  public function parseFromChild(dom\element $el) {

    return $this->parseElementSelf($el);
  }

  public function parseComponent(dom\element $el, reflector\domed $parent = null) {

    if (!$this->allowComponent()) {

      $this->throwException(sprintf('Component building not allowed with %s', $el->asToken()));
    }

    if (!$parent) $parent = $this;

    return $this->createComponent($this, $parent, $el);
  }

  /**
   *
   * @return parser\reflector\domed
   */
  protected function getParent() {

    return $this->parent;
  }

  /**
   * Set local parsers, with associated namespaces
   * @param parser\reflector\domed $parser
   * @param array $aNS
   */
  protected function setParser(reflector\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }

  protected function loadParser($sNamespace, $sParser = 'element') {

    return $this->validateParser($sNamespace, $sParser);
  }

  public function getLastElement() {

    return parent::getLastElement();
  }

  /**
   * Get a file relative to the source file's directory
   * @param string $sPath
   * @return fs\file
   */
  protected function getSourceFile($sPath) {

    return $this->getControler(static::FILE_MANAGER)->getFile($sPath, $this->getParser()->getSourceDirectory());
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
