<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\core\window, sylma\modules\html, sylma\template;

class JSON extends context\Basic implements dom\domable, window\scripted {

  const PARSER_MANAGER = 'parser';

  //const PARSER_MANAGER = 'parser';
  public function __construct() {

    //parent::__construct($aArray, $aNS, $parent);
  }

  public function setScript(core\request $path, core\argument $post, $sContext = '') {

    //$path->parse();
    $parser = \Sylma::getManager(self::PARSER_MANAGER);
    $messages = new html\context\Messages;
    $parser->setContext('messages', $messages);

    $contexts = new core\argument\Readable(array(
      'messages' => $messages,
      'js' => new html\context\JS(array(
        'load' => new template\binder\context\Load,
      )),
    ));

//dsp($path->getArguments());
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

    //dsp($sResult);
//dsp($sResult, $path->getFile(), $path->getArguments());
    $this->setArray(array(
      'content' => (string) $sResult,
      'objects' => $contexts->get('js/load/objects', false),
      'messages' => $contexts->get('messages'),
    ));
  }

  public function asString() {

    //$this->set('messages', $this->get('messages')->asString());

    return $this->asJSON();
  }

}

