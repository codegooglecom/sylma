<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="*" mode="reference/js">
    <js:include>/#sylma/ui/Clonable.js</js:include>
    <js:include>/#sylma/crud/Group.js</js:include>
    <js:include>/#sylma/crud/fieldset/Container.js</js:include>
    <js:include>/#sylma/crud/fieldset/Row.js</js:include>
    <js:include>/#sylma/crud/fieldset/RowMovable.js</js:include>
    <js:include>/#sylma/crud/fieldset/RowForm.js</js:include>
    <js:include>/#sylma/crud/fieldset/Template.js</js:include>
  </tpl:template>

  <tpl:template match="sql:reference" mode="container">

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
    <button type="button" js:name="add" js:class="sylma.ui.Clonable">
      <js:event name="click">
        %parent%.addTemplate();
      </js:event>
      <tpl:text>+</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="sql:reference" mode="template">
    <div js:name="template" js:class="sylma.crud.fieldset.Template" class="form-reference clearfix sylma-hidder" style="display: none">
      <tpl:apply select="static()" mode="empty"/>
      <tpl:apply mode="row/remove"/>
    </div>
  </tpl:template>

</tpl:collection>
