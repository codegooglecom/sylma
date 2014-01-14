<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="sql:table" sql:ns="ns" mode="empty">
    <tpl:apply select="* ^ sql:foreign" mode="container/empty"/>
  </tpl:template>

  <tpl:template match="sql:table" sql:ns="ns">
    <tpl:apply select="* ^ sql:foreign" mode="container"/>
  </tpl:template>

  <tpl:template match="sql:id" mode="label"/>
  <tpl:template match="sql:id" mode="input/empty">
    <tpl:register/>
    <tpl:apply mode="input/hidden/empty"/>
  </tpl:template>

  <tpl:template match="sql:id" mode="input">
    <tpl:register/>
    <tpl:apply mode="input/hidden"/>
  </tpl:template>

  <tpl:template match="sql:table" sql:ns="ns" mode="update">
    <div js:class="sylma.crud.fieldset.Row" class="form-reference clearfix sylma-hidder sylma-visible">
      <tpl:apply/>
      <button type="button" class="right">
        <js:event name="click">
          %object%.remove();
        </js:event>
        <tpl:text>-</tpl:text>
      </button>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="reference/js">
    <js:include>/#sylma/crud/Group.js</js:include>
    <js:include>/#sylma/crud/fieldset/Container.js</js:include>
    <js:include>/#sylma/crud/fieldset/Row.js</js:include>
    <js:include>/#sylma/crud/fieldset/RowMovable.js</js:include>
    <js:include>/#sylma/crud/fieldset/Template.js</js:include>
  </tpl:template>

  <tpl:template match="sql:reference" mode="container" sql:ns="ns">
    <tpl:apply mode="reference/js"/>
    <fieldset js:class="sylma.crud.fieldset.Container" js:parent-name="fieldset">
      <js:name>
        <tpl:read select="alias()"/>
      </js:name>
      <js:option name="useID">
        <tpl:read select="use-id()"/>
      </js:option>
      <tpl:apply mode="legend"/>
      <tpl:apply mode="template/add"/>
      <tpl:apply mode="template"/>
      <div js:class="sylma.crud.Group" js:name="content">
        <tpl:apply select="ref()" mode="update"/>
      </div>
    </fieldset>
  </tpl:template>

  <tpl:template match="sql:reference" mode="template/add">
    <button type="button" js:class="sylma.ui.Base">
      <js:event name="click">
        %parent%.addTemplate();
      </js:event>
      <tpl:text>+</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="sql:reference" mode="template">
    <div js:name="template" js:class="sylma.crud.fieldset.Template" class="form-reference clearfix sylma-hidder" style="display: none">
      <tpl:apply select="static()" mode="empty"/>
      <button type="button" class="right">
        <js:event name="click">
          %object%.remove();
        </js:event>
        <tpl:text>-</tpl:text>
      </button>
    </div>
  </tpl:template>


</tpl:collection>
