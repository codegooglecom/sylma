<?php

namespace sylma\core;

interface argumentable {
  
  /**
   * Allow exporting object as argument. Usefull for exporting as XML
   * Argument exported with this method can contain object that will be argumented with @method core\argument::normalize()
   * 
   * @return core\argument An argument representing the object
   */
  function asArgument();
}
