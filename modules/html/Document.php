<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs, sylma\core\functions;

class Document extends parser\action\handler\Basic {

  private $head = null;
  protected $result = null;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setContexts(array(
      'css' => new context\CSS,
      'js' => new context\JS,
      'js/load' => new parser\js\context\Load,
      'message' =>  new context\Messages,
      //'title' =>  new parser\context\Basic,
    ));

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

  protected function loadContexts() {

    $action = $this->getAction();

    foreach ($this->getContexts() as $sName => $context) {

      switch ($sName) {

        case parser\action\cached::CONTEXT_DEFAULT : break;
        case 'message' :

          if ($messages = $this->result->getx('//html:div[@id="messages"]', array(), false)) {

            $messages->add($context->asDOM());
          }
          
          break;

        default :

          if ($context instanceof dom\domable) $content = $context;
          else $content = $context->asArray();

          if ($content) $this->addHeadContent($content);
      }
    }
  }

  protected function loadSystemInfos(dom\handler $doc) {

    $body = $doc->getx('//html:body');

    require_once('core/functions/Numeric.php');

    $content = $this->createArgument(array(
      'ul' => array(
        '#li' => array(
          'user : ' . $this->getControler('user')->getName(),
          'time : ' . functions\numeric\formatFloat($this->getControler('init')->getElapsedTime()),
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

    if ($doc) {

      $this->result = $doc;
      $doc->registerNamespaces($this->getNS());

      if ($this->getControler('user')->isPrivate()) $this->loadSystemInfos($doc);

      $this->loadContexts();

      $result = $this->loadHeaders('text/html') . "\n" . $this->cleanResult($doc);
    }

    return $result;
  }
}

require_once('core/module/Domed.php');

class Cleaner extends core\module\Domed {

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  public function clean(dom\handler $doc) {

    require_once('dom/handler.php');

    $cleaner = $this->getTemplate('cleaner.xsl');

    $cleaned = $cleaner->parseDocument($doc);

    $iMode = 0;
    if (\Sylma::read('initializer/output/indent')) $iMode = dom\handler::STRING_INDENT;

    return $cleaned->asString($iMode); // | dom\handler::STRING_HEAD
  }
}