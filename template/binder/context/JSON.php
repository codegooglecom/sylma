<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\core\window;

class JSON extends context\Basic implements dom\domable, window\scripted {

  //const PARSER_MANAGER = 'parser';

  public function setScript(core\request $path, $sContext = '') {

    $parser = $this->getManager(self::PARSER_MANAGER);
    $messages = new \sylma\modules\html\context\Messages;
    $parser->setContext('messages', $messages);

    $contexts = $this->createArgument(array(
      'messages' => $messages,
    ));

    try {

      $sResult = $parser->load($path->getFile(), array(
        'arguments' => $path->getArguments(),
        'contexts' => $contexts,
      ));
    }
    catch (core\exception $e) {

      $sResult = false;
      $e->save(false);
    }

    $this->setArguments(array(
      'result' => (bool) $sResult,
      'messages' => $contexts->get('messages'),
    ));
  }

  public function asString() {

    $this->setArgument('messages', $this->getArgument('messages')->asString());

    return $this->getArguments()->asJSON();
  }

}

