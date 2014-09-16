<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\parser\context, sylma\parser\action, sylma\dom, sylma\core\window, sylma\modules\html, sylma\template;

class JSON extends window\classes\Container implements window\scripted, window\action {

  protected $action;
  const PARSER_MANAGER = 'parser';

  public function __construct() {

    if (\Sylma::isAdmin()) {

      sleep($this->getManager('init')->read('debug/delay'));
    }
  }

  public function setScript(core\request $path, $sContext = '') {

    //$path->parse();
    $bError = null;
    $parser = \Sylma::getManager('parser');
    $messages = new html\context\Messages;

    $contexts = new core\argument\Readable(array(
      'errors' => $this->initMessages(),
      'messages' => $messages,
      'js-common' => new html\context\JS(),
      'js' => new html\context\JS(array(
        'load' => new template\binder\context\Load,
      )),
    ));

    $this->setContexts($contexts);
    $parser->setContext('messages', $messages);
    $debug = $this->getManager('init')->getArgument('window');

    try {

      $result = $this->runScript($path->getFile(), $path->getArguments(), $this->getManager('init')->loadPOST(true), $debug);
    }
    catch (core\exception $e) {

      $result = '';
      $e->save(false);
      $bError = true;

      $messages->add(array('content' => 'An error has occured'));
    }

    if (\Sylma::isAdmin()) {

      $errors = $contexts->get('errors');
    }
    else {

      $errors = null;
    }

    $classes = $contexts->get('js/classes', false);

    $this->setSettings(array(
      'content' => $this->formatContent($result),
      'objects' => $contexts->get('js/load/objects', false),
      'classes' => $classes ? $classes->asStringVar() : null,
      'error' => $bError,
      'errors' => $errors,
      'messages' => $contexts->get('messages'),
    ));
  }

  protected function formatContent($result) {

    $this->setDirectory(__FILE__);

    return $result instanceof dom\document ? $this->cleanResult($result, $this->getFile('cleaner.xsl')) : $result;
  }

  public function setAction(action\handler $action) {

    $this->action = $action;
  }

  protected function getAction() {

    return $this->action;
  }

  protected function loadAction(action\handler $action) {

    $contexts = new core\argument\Readable(array(
      'errors' => $this->initMessages(),
      'messages' => new html\context\Messages,
      'js' => new html\context\JS(),
    ));

    $action->setContexts($contexts);
    $context = new window\classes\Context(\Sylma::getManager('init'));
    $bError = false;

    try {

      $context->setAction($action, 'default');
      $sResult = $context->asString();
    }
    catch (core\exception $e) {

      $e->save(false);
      $sResult = '';
      $bError = true;
    }

    $this->setSettings(array(
      'content' => $sResult,
      'errors' => $contexts->get('errors'),
      'error' => $bError,
      'messages' => $contexts->get('messages'),
    ));
  }

  public function asString() {

    header('Vary: Accept');

    if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {

      \Sylma::getManager('init')->setHeaderContent('application/json');

    } else {

      \Sylma::getManager('init')->setHeaderContent('text/plain');
    }

    if ($action = $this->getAction()) {

      $this->loadAction($action);
    }

    return $this->getSettings()->asJSON();
  }
}

