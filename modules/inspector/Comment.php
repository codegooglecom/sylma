<?php

class InspectorComment extends InspectorReflector {
  
  const LINE_BREAK = "\n";
  
  const RETURN_PREG = '/^\s*([\w\|]+)+(?:\s+(.+))?$/';
  const PARAMETER_PREG = '/^\s*([\w\|]+)+\s*\$(\w+)(.+)$/';
  const COMMENT_PREG = '/[\s*]*(?:@(\w+))?(.*)/';
  
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
      
      preg_match(self::COMMENT_PREG, $sLine, $aMatch);
      
      if ($aMatch) {
        
        $sToken = $aMatch[1];
        $sValue = $aMatch[2];
        
        if ($sToken) {
          
          if ($sToken == 'param') {
            
            $bOptional = false;
            
            if ($sValue{0} == '?') {
              
              $sValue = substr($sValue, 1);
              $bOptional = true;
            }
            
            preg_match(self::PARAMETER_PREG, $sValue, $aMatch);
            
            $sCast = array_val(1, $aMatch);
            $sName = array_val(2, $aMatch);
            $sValue = array_val(3, $aMatch);
            
            if (!array_key_exists('#parameter', $aProperties)) $aProperties['#parameter'] = array();
            $aProperties['#parameter'][] = array(
              '@name' => $sName,
              '@required' => booltostr(!$bOptional),
              '#cast' => (array) explode('|', $sCast),
              'description' => trim($sValue),
            );
          }
          else if ($sToken == 'return') {
            
            preg_match(self::RETURN_PREG, $sValue, $aMatch);
            
            $sCast = array_val(1, $aMatch, 'unknown');
            $sValue = array_val(2, $aMatch, '[empty]');
            
            $aProperties['return'] = array(
              '#cast' => (array) explode('|', $sCast),
              'description' => trim($sValue),
            );
          }
          else {
            
            $aProperties[$sToken] = trim($sValue);
          }
        }
        else {
          
          $sResult .= trim($sValue) . self::LINE_BREAK;
        }
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