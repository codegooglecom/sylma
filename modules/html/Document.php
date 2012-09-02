<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs, sylma\core\functions;

require_once('parser/action/handler/Basic.php');

require_once('parser/context/Basic.php');

class Document extends parser\action\handler\Basic {

  private $head = null;
  protected $result = null;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setContexts(array(
      'css' => new parser\context\Basic,
      'js' => new parser\context\Basic,
      'title' =>  new parser\context\Basic,
    ));

    $this->setNamespaces(array(
      'html' => \Sylma::read('namespaces/html'),
    ));

    parent::__construct($file, $aArguments, $base);
  }

  protected function addJS(parser\context $context) {

    if ($head = $this->getHead()) {
      
      foreach ($context->asArray() as $mContext) {

        $script = $head->addElement('script', null, array(
          'type' => 'text/javascript',
        ));

        if ($mContext instanceof fs\file) {

          $script->setAttribute('src', (string) $mContext);
        }
        else {

          $script->add($mContext);
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

    $this->addCSS($this->getContext('css'));
    $this->addJS($this->getContext('js'));
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

    return $cleaner->clean($doc);
  }

  public function asString() {

    $sProlog = $this->loadHeaders('text/html'); // 'application/xhtml+xml'

    $doc = parent::asDOM();

    $this->result = $doc;

    $doc->registerNamespaces($this->getNS());

    $this->loadSystemInfos($doc);
    $this->loadContexts();

    return $sProlog . "\n" . $this->cleanResult($doc);
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

    return $cleaned->asString(dom\handler::STRING_INDENT); // | dom\handler::STRING_HEAD
  }
}