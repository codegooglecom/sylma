<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\languages\php;

class Domed extends core\module\Argumented {

  const CLASS_PREFIX = 'class';

  protected $file;
  protected $document;
  protected $aMethods = array();

  protected $sName;
  protected $bElement = false;

  public function __construct(Controler $controler, fs\file $file) {

    $this->setControler($controler);
    $this->setFile($file);

    $this->setArguments($file->getArgument());
    $this->getArguments()->registerToken('method', 'method', 'path');
    $this->getArguments()->registerToken('element', 'method', 'element');

    if ($sElement = $this->readArgument('element', false)) {

      $this->useElement(true);
      $this->setNamespace($sElement, self::CLASS_PREFIX, false);
    }

    $this->setNamespace($this->readArgument('namespace'), 'php', false);
    $this->setName($this->readArgument('name'));
  }

  public function useElement($bValue = null) {

    if (!is_null($bValue)) $this->bElement = $bValue;
    return $this->bElement;
  }

  public function getFile() {

    return $this->file;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  public function getName($bFull = true) {

    if ($bFull) $sResult = $this->getNamespace('php') . '\\' . $this->sName;
    else $sResult = $this->sName;

    return $sResult;
  }

  protected function setName($sNamespace) {

    $this->sName = $sNamespace;
  }

  public function parseCall(dom\element $el, php\basic\_ObjectVar $var) {

    if (!$el->isElement('call', $this->getNamespace())) {

      $this->throwException(sprintf('Bad call name : %s', $el->asToken()));
    }

    $sMethod = $el->getAttribute('name');

    if (!$sMethod) {

      $this->throwException(sprintf('Invalid element, no method defined for call in %s', $el->asToken()));
    }

    return $this->getControler()->getParent()->runObject($el, $var, $this->getMethod($sMethod));
  }

  public function loadCall(php\basic\_ObjectVar $var, Method $method, dom\collection $args) {

    $aArguments = $this->parseArguments($args);

    $call = $method->reflectCall($var->getControler(), $var, $aArguments);

    return $call;
  }

  protected function parseNode(dom\node $node) {

    return $this->getControler()->getParent()->parse($node);
  }

  protected function parseArgument(dom\element $el, $iKey) {

    if (!$mKey = $el->readAttribute('argument', $this->getNamespace(), false)) {

      $mKey = $iKey;
    }

    if ($el->getNamespace() == $this->getNamespace()) {

      if ($el->getName() != 'argument') {

        $this->throwException(sprintf('Invalid %s, argument expected', $el->asToken()));
      }

      if ($el->countChildren() > 1) {

        $this->throwException(t('There shouldn\'t have more than one child in %s', $el->asToken()));
      }

      $mResult = $this->parseNode($el->getFirst());
    }
    else {

      $mResult = $this->parseNode($el);
    }

    return $this->getControler()->createArgument(array(
      'name' => $mKey,
      'value' => $mResult,
    ));
  }

  protected function parseArguments(dom\collection $children) {

    $aResult = array();
    $iKey = 0;

    while ($child = $children->current()) {

      switch ($child->getType()) {

        case dom\node::TEXT :

          $aResult[] = $this->parseNode($child);

        break;

        case dom\node::ELEMENT :

          if ($child->getNamespace() == $this->getNamespace()) {

            if (in_array($child->getName(), array('call'))) {

              break 2;
            }
          }
          else if ($this->getControler()->getParent()->useNamespace($child->getNamespace())) {

            // if not special call (with parent namespace), use as argument
            // TODO : bad bad bad
            if (in_array($child->getName(), array('if', 'if-not'))) {

              break 2;
            }
          }

          $arg = $this->parseArgument($child, $iKey);
          $child->remove();

          $aResult[$arg->read('name')] = $arg->get('value', false);

        break;

        default :

          $this->throwException(sprintf('Cannot use %s, valid argument expected', $child->asToken()));
      }

      $children->next();
      $iKey++;
    }

    return $aResult;
  }

  protected function getMethod($sName) {

    if (!array_key_exists($sName, $this->aMethods)) {

      $this->aMethods[$sName] = $this->loadMethod($sName);
    }

    return $this->aMethods[$sName];
  }

  public function loadMethod($sMethod, $sToken = 'method') {

    $controler = $this->getControler();

    $arg = $this->getArgument('#' . $sToken . ':'. $sMethod, null, false);

    if (!$arg) {

      $this->throwException(sprintf('Cannot find method %s', $sMethod));
    }

    $result = $controler->create('method', array($this, $arg));

    return $result;
  }

  /**
   * Namespace with prefix php is used here as PHP namespaces with anti-slash instead of slash
   * @param string|null $sPrefix
   * @return string
   */
  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = $this->getFile()->asToken();

    parent::throwException($sMessage, $mSender, $iOffset);
  }

}