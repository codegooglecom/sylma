<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"

  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
>

  <tpl:template match="*" mode="move/resources">

    <js:include>/#sylma/ui/Clonable.js</js:include>
    <js:include>/#sylma/crud/Group.js</js:include>
    <js:include>/#sylma/crud/fieldset/Container.js</js:include>
    <js:include>/#sylma/crud/fieldset/Row.js</js:include>
    <js:include>/#sylma/crud/fieldset/RowMovable.js</js:include>
    <js:include>Scroller.js</js:include>

    <le:context name="css">
      <le:file>scroller.less</le:file>
    </le:context>

  </tpl:template>

  <tpl:template match="*" mode="row/move">
    <button type="button" js:node="move">
      <js:event name="click" arguments="e">
        e.stopPropagation();
      </js:event>
      <js:event name="mousedown" arguments="e">
        %object%.drag(e);
      </js:event>
      <tpl:text>↕</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="*" mode="move/scroller">

    <div js:class="sylma.crud.move.Scroller" js:name="scroller" class="scroller sylma-hidder">

      <js:event name="mouseout">
        %object%.stopScroll();
      </js:event>
      <div js:node="top" class="top">
        <js:event name="mouseenter">
          %object%.scroll(-1,e);
        </js:event>
      </div>
      <div js:node="bottom" class="bottom">
        <js:event name="mouseenter">
          %object%.scroll(1,e);
        </js:event>
      </div>
    </div>

  </tpl:template>

</tpl:templates>
