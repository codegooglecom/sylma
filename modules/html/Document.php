<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser\action, sylma\template\binder, sylma\dom, sylma\storage\fs, sylma\core\functions;

class Document extends action\handler\Basic {

  private $head = null;
  protected $result = null;

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    // global context is usefull for free action (without parent)
    $messages = new context\Messages;
    $this->getManager('parser')->setContext('messages', $messages);

    $this->setContexts($this->createArgument(array(
      'css' => new context\CSS,
      'js' => new context\JS(array(
        'load' => new binder\context\Load,
      )),
      //'js-classes' => new binder\context\Classes,
      //'js/load' => new js\context\Load,
      'messages' =>  $messages,
      //'title' =>  new parser\context\Basic,
    )));

    $this->setNamespaces(array(
      'html' => \Sylma::read('namespaces/html'),
    ));

    parent::__construct($file, $aArguments, $base);
  }

  protected function addHeadContent($context) {

    if ($head = $this->getHead()) $head->add($context);
  }

  protected function getHead() {

    if (!$this->head) {

      if ($this->result) {

        $this->head = $this->result->getx('html:head');
      }
    }

    return $this->head;
  }

  protected function loadHeaders($sMime) {

    $sResult = '';
    $sCharset = 'utf-8';

    if($sMime == "application/xhtml+xml") {

      $sResult = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    }
    else {

      $sResult = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    }

    $this->getControler('init')->setHeaderContent($sMime, $sCharset);
    $this->getControler('init')->setHeaderCache(3600);

    //header("Vary: Accept");

    return $sResult;
  }

  protected function loadContexts(dom\document $doc) {

    foreach ($this->getContexts()->query() as $sName => $context) {

      $this->loadContext($sName, $context, $doc);
    }
  }

  protected function loadContext($sName, $context, dom\document $doc) {

    switch ($sName) {

      case action\cached::CONTEXT_DEFAULT : break;
      case 'messages' :

        if ($messages = $this->result->getx('//html:div[@id="messages"]', array(), false)) {

          $messages->add($context->asDOM());
        }
        else if (\Sylma::read('debug/enable')) {

          echo '<h1>No container for messages</h1>';
        }

        break;

      default :

        if ($context instanceof dom\domable) $content = $context;
        else $content = $context->asArray();

        if ($content) $this->addHeadContent($content);
    }
  }

  protected function loadSystemInfos(dom\handler $doc) {

    $body = $doc->getx('//html:body');

    require_once('core/functions/Numeric.php');
    $parser = $this->getManager('parser');
    $aBuilded = $parser->aBuilded;
    $aLoaded = $parser::$aLoaded;

    $iLoaded = 0;
    array_walk($aLoaded, function (&$item, $key) use (&$iLoaded) {
      $iLoaded += $item;
      $item = "$key : ($item)";
    });

    $content = $this->createArgument(array(
      'ul' => array(
        '#li' => array(
          'user : ' . $this->getControler('user')->getName(),
          'time : ' . functions\numeric\formatFloat($this->getControler('init')->getElapsedTime()),
          'builded : ' . count($aBuilded),
          array(
            'ul' => array(
              '#li' => array_map('strval', $aBuilded),
            ),
          ),
          'loaded : ' . $iLoaded,
          array(
            'ul' => array(
              '#li' => $aLoaded,
            ),
          ),
        ),
      ),
    ), $this->getNamespace('html'));

    $system = $body->addElement('div', null, array('id' => 'sylma-system'));
    $system->addElement('div', $content);
  }

  protected function cleanResult(dom\handler $doc) {

    $cleaner = new Cleaner;

    if (\Sylma::read('debug/html')) {

      $sHTML = \Sylma::read('namespaces/html');
      $els = $doc->queryx("//*[namespace-uri() != '$sHTML']", array(), false);

      if ($els->length) {

        $this->dsp($els);
        $this->throwException('Foreign element\'s namespace in HTML output');
      }
    }

    return $cleaner->clean($doc);
  }

  public function asString() {

    $result = null;
    $doc = parent::asDOM();

    if ($doc && !$doc->isEmpty()) {

      $this->result = $doc;
      $doc->registerNamespaces($this->getNS());

      if ($this->getControler('user')->isPrivate()) {

        $this->loadSystemInfos($doc);
      }

      //$this->getContext('message')->add(array('content' => $this->getManager('init')->getStats()));

      $this->loadContexts($doc);

      $result = $this->loadHeaders('text/html') . "\n" . $this->cleanResult($doc);
    }
    else if (\Sylma::read('debug/enable')) {

      echo '<h2>No result document</h2>';
    }

    return $result;
  }
}