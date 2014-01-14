<?php
    return new \sylma\core\argument\parser\Cached(array(
'debug' => array(
  'show' => '0',
  'run' => '1',
  'update' => '0'),
'cache' => array(
  'elements' => array(
    'directory' => 'getDirectory',
    'file' => 'getFile',
    'document' => 'getDocument',
    'template' => 'getTemplate',
    'argument' => 'createArgument',
    'manager' => 'getManager'),
  'namespace' => 'http://www.sylma.org/parser/action/basic',
  'class' => '\sylma\parser\action\cached\Document'),
'template' => array(
  'indent' => '0'),
'php' => array(
  'classes' => array(
    'string' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_String.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_String'),
    'array' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Array.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Array'),
    'numeric' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Numeric.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Numeric'),
    'object' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Object.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Object'),
    'class' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Class.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Class'),
    'call-method' => array(
      'file' => '\sylma\parser\languages\php\basic\CallMethod.php',
      'name' => '\sylma\parser\languages\php\basic\CallMethod'),
    'call' => array(
      'file' => '\sylma\parser\languages\php\basic\_Call.php',
      'name' => '\sylma\parser\languages\php\basic\_Call'),
    'object-var' => array(
      'file' => '\sylma\parser\languages\php\basic\_ObjectVar.php',
      'name' => '\sylma\parser\languages\php\basic\_ObjectVar'),
    'simple-var' => array(
      'file' => '\sylma\parser\languages\php\basic\_ScalarVar.php',
      'name' => '\sylma\parser\languages\php\basic\_ScalarVar'),
    'template' => array(
      'file' => '\sylma\parser\languages\php\basic\Template.php',
      'name' => '\sylma\parser\languages\php\basic\Template'),
    'function' => array(
      'file' => '\sylma\parser\languages\php\basic\_Function.php',
      'name' => '\sylma\parser\languages\php\basic\_Function'),
    'closure' => array(
      'file' => '\sylma\parser\languages\php\basic\_Closure.php',
      'name' => '\sylma\parser\languages\php\basic\_Closure'),
    'instanciate' => array(
      'file' => '\sylma\parser\languages\php\basic\Instanciate.php',
      'name' => '\sylma\parser\languages\php\basic\Instanciate'),
    'assign' => array(
      'file' => '\sylma\parser\languages\common\basic\Assign.php',
      'name' => '\sylma\parser\languages\common\basic\Assign'),
    'assign-concat' => array(
      'file' => '\sylma\parser\languages\php\basic\assign\Concat.php',
      'name' => '\sylma\parser\languages\php\basic\assign\Concat'),
    'insert' => array(
      'file' => '\sylma\parser\languages\php\basic\Insert.php',
      'name' => '\sylma\parser\languages\php\basic\Insert'),
    'interface' => array(
      'file' => '\sylma\parser\languages\php\basic\_Interface.php',
      'name' => '\sylma\parser\languages\php\basic\_Interface'),
    'method' => array(
      'file' => '\sylma\parser\languages\php\basic\Method.php',
      'name' => '\sylma\parser\languages\php\basic\Method'),
    'line' => array(
      'file' => '\sylma\parser\languages\php\basic\_Line.php',
      'name' => '\sylma\parser\languages\php\basic\_Line'),
    'instruction' => array(
      'file' => '\sylma\parser\languages\common\basic\Instruction.php',
      'name' => '\sylma\parser\languages\common\basic\Instruction'),
    'null' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Null.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Null'),
    'boolean' => array(
      'file' => '\sylma\parser\languages\php\basic\instance\_Boolean.php',
      'name' => '\sylma\parser\languages\php\basic\instance\_Boolean'),
    'concat' => array(
      'file' => '\sylma\parser\languages\php\basic\Concat.php',
      'name' => '\sylma\parser\languages\php\basic\Concat'),
    'condition' => array(
      'file' => '\sylma\parser\languages\php\basic\Condition.php',
      'name' => '\sylma\parser\languages\php\basic\Condition'),
    'switch' => array(
      'file' => '\sylma\parser\languages\php\basic\_Switch.php',
      'name' => '\sylma\parser\languages\php\basic\_Switch'),
    'case' => array(
      'file' => '\sylma\parser\languages\php\basic\_Case.php',
      'name' => '\sylma\parser\languages\php\basic\_Case'),
    'test' => array(
      'file' => '\sylma\parser\languages\php\basic\Test.php',
      'name' => '\sylma\parser\languages\php\basic\Test'),
    'loop' => array(
      'file' => '\sylma\parser\languages\php\basic\_Foreach.php',
      'name' => '\sylma\parser\languages\php\basic\_Foreach'),
    'cast' => array(
      'file' => '\sylma\parser\languages\php\basic\Cast.php',
      'name' => '\sylma\parser\languages\php\basic\Cast'),
    'operator' => array(
      'file' => '\sylma\parser\languages\common\basic\Operator.php',
      'name' => '\sylma\parser\languages\common\basic\Operator'),
    'group' => array(
      'file' => '\sylma\parser\languages\common\basic\Group.php',
      'name' => '\sylma\parser\languages\common\basic\Group'),
    'expression' => array(
      'file' => '\sylma\parser\languages\common\basic\Expression.php',
      'name' => '\sylma\parser\languages\common\basic\Expression'),
    'caller' => array(
      'file' => '\sylma\parser\languages\common\basic\Caller.php',
      'name' => '\sylma\parser\languages\common\basic\Caller'))),
'classes' => array(
  'window' => array(
    'file' => '\sylma\parser\action\compiler\Window.php',
    'name' => '\sylma\parser\action\compiler\Window'),
  'cached' => array(
    'file' => '\sylma\parser\action\cached\Document.php',
    'name' => '\sylma\parser\action\cached\Document'),
  'compiler' => array(
    'file' => '\sylma\parser\action\compiler\Main.php',
    'name' => '\sylma\parser\action\compiler\Main'),
  'reflector' => array(
    'file' => '\sylma\parser\action\compiler\Reflector.php',
    'name' => '\sylma\parser\action\compiler\Reflector'),
  'document' => array(
    'file' => '\sylma\dom\basic\handler\Rooted.php',
    'name' => '\sylma\dom\basic\handler\Rooted'),
  'path' => array(
    'file' => '\sylma\core\request\Basic.php',
    'name' => '\sylma\core\request\Basic'),
  'request' => array(
    'file' => '\sylma\core\request\Builder.php',
    'name' => '\sylma\core\request\Builder'),
  'context' => array(
    'file' => '\sylma\modules\html\context\Basic.php',
    'name' => '\sylma\modules\html\context\Basic'))));
  