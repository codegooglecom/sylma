<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs, \sylma\parser\action\php;

require_once('Reflector.php');
require_once('parser/domed.php');

class Domed extends Reflector implements parser\domed {

  const PREFIX = 'le';
  const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';

  const CLASS_DEFAULT = '\sylma\parser\action\cached\Document';
  const CLASS_PREFIX = 'element';

  const WINDOW_ARGS = 'classes/php';

  /**
   * See @method setFile()
   * @var storage\fs
   */
  private $document;

  private $bTemplate = false;
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

  public function __construct(Controler $controler, dom\handler $doc, fs\directory $dir) {

    $this->setDocument($doc);
    $this->setControler($controler);
    $this->setNamespace($controler->getNamespace(), self::PREFIX);
    $this->setDirectory($dir);

    $sClass = $this->loadClass($doc);

    $window = $this->getControler()->create('window', array($this, $controler->getArgument(self::WINDOW_ARGS), $sClass));
    $this->setWindow($window);

    $caller = $this->getControler('caller');
    $caller->setParent($this);

    $this->setInterface($caller->getInterface($sClass));

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

  public function setInterface(parser\caller\Domed $interface) {

    $this->interface = $interface;
  }

  private function getParser($sUri) {

    return array_key_exists($sUri, $this->aParsers) ? $this->aParsers[$sUri] : null;
  }

  protected function loadClass(dom\handler $doc) {

    if (!$sResult = $doc->getRoot()->readAttribute('class', null, false)) {

      $sResult = self::CLASS_DEFAULT;
    }

    return $sResult;
  }

  protected function extractArguments(dom\element $settings) {

    $aResult = array();
    $args = $settings->queryx('le:argument', array(), false);

    foreach ($args as $arg) {


    }

    return $aResult;
  }

  protected function parseSettings(dom\element $settings) {

    $aResult = array();

    foreach ($settings->getChildren() as $el) {

      if ($el->getNamespace() == $this->getNamespace()) {

        switch ($el->getName()) {

          case 'argument' :

            $aResult += $this->reflectSettingsArgument($el);

          break;
          case 'name' : $this->setName($el->read()); break;

          case 'return' : $this->setReturn($el); break;

          default : $this->parseElement($el);
        }
      }
      else {

        $this->parseElement($el);
      }
    }

    return $aResult;
  }

  protected function setReturn(dom\element $el) {

    $sFormat = $el->readAttribute('format');

    $this->return = $this->getWindow()->stringToInstance($sFormat);
  }

  protected function getReturn() {

    return $this->return;
  }

  protected function parseDocument(dom\document $doc) {

    $aResult = array();

    if ($doc->isEmpty()) {

      $this->throwException(t('empty doc'));
    }

    $doc->registerNamespaces($this->getNS());

    $settings = $doc->getx(self::PREFIX . ':settings', $this->getNS(), false);

    // arguments

    if ($settings) {

      $aArguments = $this->extractArguments($settings);
      $this->getWindow()->add($this->parseSettings($settings));
      $settings->remove();
    }

    $aResult = $this->parseChildren($doc);

    return $aResult;
  }

  protected function parseNode(dom\node $node) {

    $mResult = null;

    switch ($node->getType()) {

      case dom\node::ELEMENT :

        $mResult = $this->parseElement($node);

      break;

      case dom\node::TEXT :

        $mResult = $this->getWindow()->create('string', array($this->getWindow(), (string) $node));

      break;

      case dom\node::COMMENT :

      break;

      default :

        $this->throwException(txt('Unknown node type : %s', $node->getType()));
    }

    return $mResult;
  }

  public function parse(dom\node $node) {

    return $this->parseNode($node);
  }

  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  protected function parseElement(dom\element $el) {

    $sNamespace = $el->getNamespace();
    $mResult = null;

    if ($sNamespace == $this->getNamespace()) {

      $mResult = $this->parseElementAction($el);
    }
    else {

      $mResult = $this->parseElementForeign($el);
    }

    return $mResult;
  }

  protected function useTemplate($bValue = null) {

    if (!is_null($bValue)) $this->bTemplate = $bValue;

    return $this->bTemplate;
  }

  /**
   *
   * @param dom\element $el
   * @return dom\node|array|null
   */
  protected function parseElementForeign(dom\element $el) {

    $mResult = null;

    if ($el->getNamespace() == $this->getNamespace('element')) {

      $mResult = $this->parseElementSelf($el);
    }
    else if ($parser = $this->getParser($el->getNamespace())) {

      $mResult = $parser->parse($el);
    }
    else {

      $this->useTemplate(true);

      $mResult = $this->getControler()->create('document');
      $mResult->addElement($el->getName(), null, array(), $el->getNamespace());

      $this->parseAttributes($el);

      $mResult->add($this->parseChildren($el));
      /*if ($el->hasChildren()) {

        foreach ($el->getChildren() as $child) {

          $mResult->add($this->parse($child));
        }
      }*/
    }

    return $mResult;
  }

  /**
   * Parse children into main context. Insert results
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\complex $el) {

    $aResult = array();

    foreach ($el->getChildren() as $child) {

      if ($child->getType() != dom\node::ELEMENT) {

        $aResult[] = $this->parseNode($child);
      }
      else if ($mResult = $this->parseElement($child)) {

        if (!$mResult instanceof dom\node) {

          $mResult = $this->getWindow()->createInsert($mResult);
        }

        $aResult[] = $mResult;
      }
    }

    return $aResult;
  }

  protected function parseElementSelf(dom\element $el) {

    $window = $this->getWindow();
    $method = $this->getInterface()->loadMethod($el->getName(), 'element');
    $aArguments = array();

    foreach ($el->getAttributes() as $attr) {

      $aArguments[$attr->getName()] = $this->parseString($attr->getValue());
    }

    $call = $method->reflectCall($window, $window->getSelf(), $aArguments);

    return $this->getInterface()->runCall($call, $el->getChildren());
  }

  protected function reflectCall(dom\element $el) {

    $window = $this->getWindow();
    $sMethod = $el->readAttribute('name');

    $method = $this->getInterface()->loadMethod($sMethod);

    return $this->getInterface()->loadCall($window->getSelf(), $method, $el->getChildren());
  }

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function parseElementAction(dom\element $el) {

    $mResult = null;

    switch ($el->getName()) {

      case 'action' : $mResult = $this->reflectAction($el); break;

      case 'call' : $mResult = $this->reflectCall($el); break;

      case 'bool' :
      case 'boolean' : $mResult = $this->reflectBoolean($el); break;

      case 'string' :

      case 'text' : $mResult = $this->reflectString($el); break;
      case 'null' : $mResult = $this->reflectNull($el); break;

      case 'array' : $mResult = $this->reflectArray($el); break;
      //case 'argument' :
      case 'test-argument' :
      case 'get-all-arguments' :
      case 'get-argument' :

        $mResult = $this->reflectArgument($el);

      break;

      // case 'get-settings' :
      case 'set-variable' :
      case 'get-variable' :
      case 'switch' :
      case 'function' :
      case 'interface' :
      break;
      case 'xquery' :
      //case 'recall' :
      case 'namespace' :
      case 'ns' :
      case 'php' :
      //case 'special' :
      //case 'controler' :

        $sName = $el->getAttribute('name');
        $mResult = $window->setControler($sName);

      break;

      case 'redirect' :

        $mResult = $window->createCall($window->getSelf(), 'getRedirect', 'core\redirect');

      break;

  // <object name="window" call="Controler::getWindow()"/>
  // <object name="redirect" call="$oRedirect"/>
  // <object name="user" call="Controler::getUser()"/>
  // <object name="path" call="$oAction-&gt;getPath()" return="true"/>
  // <object name="path-simple" call="$oAction-&gt;getPath()-&gt;getSimplePath()" return="true"/>
  // <object name="path-action" return="true" call="$oAction-&gt;getPath()-&gt;getActionPath()"/>
  // <object name="self" call="$oAction" return="true"/>
  // <object name="directory" call="$oAction-&gt;getPath()-&gt;getDirectory()" return="true"/>
  // <object name="parent-directory" call="$oAction-&gt;getPath()-&gt;getDirectory()-&gt;getParent()" return="true"/>
  // <object name="parent" call="$oAction-&gt;getParent()"/>
  // <object name="database" call="Controler::getDatabase()"/>
      case 'document' :

        //if ($el->hasChildren())

        $mResult = $this->reflectDocument($el);

      break;

      case 'template' :

      default :

        $this->throwException(txt('Unknown action element : %s', $el->asToken()));
    }

    return $mResult;
  }

  public function setParent(parser\domed $parent) {

    return null;
  }

  protected function runCall(dom\element $el, php\basic\CallMethod $call) {

    if ($el->hasChildren()) {

      $var = $call->getVar();
      $mResult = $this->parseChildrenObject($el, $var);
    }
    else {

      $mResult = $call;
    }

    return $mResult;
  }
  /**
   * Parse children into object context.
   * @param dom\element $el
   * @param php_objecte $obj
   * @return core\argumentable|array|null
   */
  protected function parseChildrenObject(dom\element $el, php\_object $obj) {

    $mResult = array();
    $window = $this->getWindow();
    //if ($el->testAttribute('return', false)) $aResult[] = $obj;

    $window->setScope($obj);
    //dspf($el);$this->throwException('hello');
    foreach ($el->getChildren() as $child) {

      $caller = $this->getControler('caller');
      $caller->setParent($this);
      $mResult[] = $caller->parse($child);
    }

    if (count($mResult) == 1) $mResult = array_pop($mResult);

    $window->stopScope();

    return $mResult;
  }

  protected function parseAttributes(dom\element $el) {

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
