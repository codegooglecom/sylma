<?php

namespace sylma\modules\html;
use sylma\core, sylma\template\binder, sylma\dom;

class Document extends core\window\classes\Container {

  private $head = null;
  protected $result = null;

  public function __construct(core\argument $args, core\argument &$contexts) {

    $this->setDirectory(__FILE__);

    $this->setArguments($args);
    $this->setSettings($this->getManager('init')->getArgument('window'));

    if (!$messages = $this->getManager('parser')->getContext('errors', false)) {

      $messages = new context\Messages;
      $this->getManager('parser')->setContext('errors', $messages);
    }

    $load = new binder\context\Load;
    $contexts = $this->createArgument(array(
      'css' => new context\CSS,
      'js' => new context\JS(array(
        'load' => $load,
      )),
      //'js-classes' => new binder\context\Classes,
      //'js/load' => new js\context\Load,
      'errors' =>  $messages,
      //'title' =>  new parser\context\Basic,
    ));

    $this->setContexts($contexts);
    $load->set('objects', new \sylma\template\binder\context\Objects());

    $this->setPaths($this->getArgument(self::CONTENT_ARGUMENT)->query());
    $this->setArgument(self::CONTENT_ARGUMENT, null);

    $this->setNamespaces(array(
      'html' => \Sylma::read('namespaces/html'),
    ));
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

    if ($this->getManager('user')->isPrivate()) {

      $this->getControler('init')->setHeaderCache(-3600, false);
      header("Cache-Control: no-cache, must-revalidate");
    }
    else {

      $this->getControler('init')->setHeaderCache(3600);
    }

    //header("Vary: Accept");

    return $sResult;
  }

  protected function loadContext($sName, $context, dom\document $doc) {

    switch ($sName) {

      case 'default' : break;
      //case action\cached::CONTEXT_DEFAULT : break;
      case 'errors' :

        if (\Sylma::isAdmin()) {

          if ($messages = $this->result->getx('//html:div[@id="messages"]', array(), false)) {

            $messages->add($context->asDOM());
          }
          else {

            echo '<h1>No container for messages</h1>';
          }
        }

        break;

      default :

        if ($context instanceof dom\domable) $content = $context;
        else $content = $context->asArray();
//dsp($sName);
//dsp($content);
        if ($content) $this->addHeadContent($content);
    }
  }

  protected function buildInfos(dom\handler $doc) {

    $body = $doc->getx('//html:body');

    $content = $this->loadInfos($doc);

    $system = $body->addElement('div', null, array('id' => 'sylma-system'));
    $system->addElement('div', $content);
  }

  protected function cleanResult(dom\handler $doc) {

    $cleaner = new Cleaner;

    if (\Sylma::read('debug/html/foreign')) {

      $sHTML = \Sylma::read('namespaces/html');
      $els = $doc->queryx("//*[namespace-uri() != '$sHTML']", array(), false);

      if ($els->length) {

        $this->dsp($els);
        $this->throwException('Foreign element\'s namespace in HTML output');
      }
    }

    return $cleaner->clean($doc);
  }

  public function prepare($sContent) {

    $sContent = substr_replace($sContent, 'xmlns="' . $this->getNamespace('html') . '" ', 6, 0);
    $doc = $this->createDocument($sContent);

    if ($doc && !$doc->isEmpty()) {

      $this->result = $doc;
      $doc->registerNamespaces($this->getNS());

      if ($this->getControler('user')->isPrivate()) {

        $this->buildInfos($doc);
      }

      //$this->getContext('errors')->add(array('content' => $this->getManager('init')->getStats()));

      $this->loadContexts($doc);

      $result = $this->loadHeaders('text/html') . "\n" . $this->cleanResult($doc);
    }
    else if (\Sylma::isAdmin()) {

      echo '<h2>No result document</h2>';
    }

    return $result;
  }
}