<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\parser\context, sylma\parser\action, sylma\dom, sylma\core\window, sylma\modules\html, sylma\template;

class JSON extends context\Basic implements dom\domable, window\scripted, window\action {

  protected $action;
  const PARSER_MANAGER = 'parser';

  //const PARSER_MANAGER = 'parser';
  public function __construct() {

    //parent::__construct($aArray, $aNS, $parent);
  }

  public function setScript(core\request $path, core\argument $post, $sContext = '') {

    //$path->parse();
    $parser = \Sylma::getManager('parser');

    $contexts = new core\argument\Readable(array(
      'errors' => $this->loadMessages(),
      'messages' => new html\context\Messages,
      'js' => new html\context\JS(array(
        'load' => new template\binder\context\Load,
      )),
    ));

    try {

      $sResult = $parser->load($path->getFile(), array(
        'arguments' => $path->getArguments(),
        'contexts' => $contexts,
        'post' => $post,
      ));
    }
    catch (core\exception $e) {

      $sResult = false;
      $e->save(false);
    }

    $classes = $contexts->get('js/classes', false);

    $this->setArray(array(
      'content' => (string) $sResult,
      'objects' => $contexts->get('js/load/objects', false),
      'classes' => $classes ? $classes->asStringVar() : null,
      'errors' => $contexts->get('errors'),
      'messages' => $contexts->get('messages'),
    ));
  }

  public function setAction(action\handler $action) {

    $this->action = $action;
  }

  protected function getAction() {

    return $this->action;
  }

  protected function loadMessages() {

    $result = new html\context\Messages;
    \Sylma::getManager('parser')->setContext('errors', $result);

    return $result;
  }

  protected function loadAction(action\handler $action) {

    $contexts = new core\argument\Readable(array(
      'errors' => $this->loadMessages(),
      'messages' => new html\context\Messages,
      'js' => new html\context\JS(),
    ));

    $action->setContexts($contexts);
    $context = new window\classes\Context(\Sylma::getManager('init'));

    try {

      $context->setAction($action, 'default');
      $sResult = $context->asString();
    }
    catch (core\exception $e) {

      $e->save(false);
      $sResult = '';
    }

    $this->setArray(array(
      'content' => $sResult,
      'errors' => $contexts->get('errors'),
      'messages' => $contexts->get('messages'),
    ));
  }

  public function asString() {

    if ($action = $this->getAction()) {

      $this->loadAction($action);
    }

    return $this->asJSON();
  }
}

