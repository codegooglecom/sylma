<?php

interface InspectorReflectorInterface {
  
  /**
   *
   */
  function log($sMessage);
  
  /**
   * This method is called in DOM parsing, and will produce an XML document representing the source code
   */
  function parse();
  
  /**
   * This method ouput the object as PHP source with hypotheticals modifications
   */
  function __toString();
}
