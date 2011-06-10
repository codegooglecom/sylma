<?php

class InspectorComment extends InspectorReflector {
  
  const LINE_BREAK = "\n";
  
  protected $sComment = '';
  protected $properties;
  
  public function __construct($sComment, $parent) {
    
    $this->parent = $parent;
    
    $this->sComment = $sComment;
    $this->sValue = $this->parseValue();
  }
  
  public function parseValue() {
    
    // preg_match('`/\*\*[\*\s]*(\s+\*\s+(.+))+`', $this->sComment, $aMatch);
    $aLines = explode(self::LINE_BREAK, $this->sComment);
    $aLines = array_slice($aLines, 1, -1);
    
    $sResult = '';
    $aProperties = array();
    
    foreach ($aLines as $sLine) {
      
      preg_match('/[\s*]*(?:@(\w+))?(.*)/', $sLine, $aMatch);
      
      if ($aMatch) {
        
        $sToken = $aMatch[1];
        $sValue = $aMatch[2];
        
        if ($sToken) $aProperties[$sToken] = trim($sValue);
        else $sResult .= trim($sValue) . self::LINE_BREAK;
      }
    }
    
    $this->properties = $this->getControler()->create('argument', array($aProperties));
    return $sResult;
  }
  
  //protected function parseValue()
  
  public function parse() {
    // dspf(array(
      // 'comment' => array(
        // 'description' => strtoxml(nl2br(trim($this->sValue))),
      // ) + $this->properties->query(),
    // ));
    return Arguments::buildDocument(array(
      'comment' => array(
        'description' => strtoxml(nl2br(trim($this->sValue))),
      ) + $this->properties->query(),
    ), $this->getControler()->getNamespace());
  }
}

class InspectorCommentClass extends InspectorComment {
  

}

class InspectorCommentMethod extends InspectorComment {
  

}

class InspectorCommentProperty extends InspectorComment {
  

}