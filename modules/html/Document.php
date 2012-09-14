<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs, sylma\core\functions;

require_once('parser/action/handler/Action.php');

require_once('parser/context/Basic.php');

class Document extends parser\action\handler\Action {

  private $head = null;
  private $body = null;

  protected $result = null;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setContexts(array(
      'css' => new parser\context\Basic,
      'js' => new parser\context\Basic,
      'js/body' => new parser\context\Basic,
      'title' =>  new parser\context\Basic,
    ));

    $this->setNamespaces(array(
      'html' => \Sylma::read('namespaces/html'),
    ));

    parent::__construct($file, $aArguments, $base);
  }

  protected function addJS(parser\context $context) {

    foreach ($context->asArray() as $mContext) {

      if ($mContext instanceof fs\file) {

        if ($this->getHead()) {

          $this->getHead()->addElement('script', null, array(
            'type' => 'text/javascript',
            'src' => (string) $mContext,
          ));
        }
      }
      else {

        if ($this->getBody()) {

          $node = $this->getBody()->createElement('script', (string) $mContext);
          $this->getBody()->shift($node);
        }
      }
    }
  }

  protected function addCSS(parser\context $context) {

    if ($head = $this->getHead()) {

      foreach ($context->asArray() as $file) {

        $head->addElement('link', null, array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'media' => 'all',
          'href' => (string) $file,
        ));
      }
    }
  }

  protected function getHead() {

    if (!$this->head) {

      if ($this->result) {

        $this->head = $this->result->getx('html:head');
      }
    }

    return $this->head;
  }

  protected function getBody() {

    if (!$this->body) {

      if ($this->result) {

        $this->body = $this->result->getx('html:body');
      }
    }

    return $this->body;
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

    $this->addCSS($this->aArguments['content']->getContext('css'));
    $this->addJS($this->aArguments['content']->getContext('js'));
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

    require_once('dom/handler.php');

    $this->setDirectory(__FILE__);
    $cleaner = $this->getTemplate('cleaner.xsl');

    $cleaned = $cleaner->parseDocument($doc);

    return $cleaned->asString(dom\handler::STRING_INDENT); // | dom\handler::STRING_HEAD
  }

  public function asString() {

    $sProlog = $this->loadHeaders('text/html'); // 'application/xhtml+xml'

    $doc = parent::asDOM();

    $this->result = $doc;

    $doc->registerNamespaces($this->getNS());

    if ($this->getControler('user')->isPrivate()) $this->loadSystemInfos($doc);
    $this->loadContexts();

    return $sProlog . "\n" . $this->cleanResult($doc);
  }
}

