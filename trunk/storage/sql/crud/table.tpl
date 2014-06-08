<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
>

  <tpl:template match="sql:table" mode="form/build">

    <tpl:variable name="action">
      <tpl:apply mode="init/action"/>
    </tpl:variable>

    <form js:class="sylma.crud.Form" class="sylma-form" action="{$action}" js:parent-name="form" method="post">

      <tpl:apply mode="form/content"/>

    </form>

  </tpl:template>

  <tpl:template mode="form/init" xmode="update">
    <sql:filter name="id">
      <le:get-argument name="id"/>
    </sql:filter>
    <input type="hidden" name="{id/alias()}" value="{id/value()}"/>
    <tpl:apply mode="title"/>
  </tpl:template>

  <tpl:template match="sql:table" mode="form/content">

    <tpl:apply mode="css"/>

    <js:include>/#sylma/crud/Form.js</js:include>

    <tpl:apply mode="form/init"/>
    <tpl:apply mode="form"/>

    <div class="form-actions">
      <tpl:apply mode="form/action"/>
    </div>
    <tpl:apply mode="form/token"/>

  </tpl:template>

  <tpl:template match="sql:table" mode="css">

    <le:context name="css">
      <le:file>/#sylma/modules/html/medias/form.css</le:file>
    </le:context>

  </tpl:template>

  <tpl:template match="sql:table" mode="form">
    <tpl:apply use="form-cols" mode="container"/>
  </tpl:template>

  <tpl:template match="sql:table" mode="form/action">
    <input type="submit" value="Envoyer"/>
  </tpl:template>

  <tpl:template match="sql:table">
    <tpl:apply select="* ^ sql:foreign" mode="container"/>
  </tpl:template>

  <tpl:template match="sql:table" mode="update">
    <div js:class="sylma.crud.fieldset.Row" class="form-reference clearfix sylma-hidder sylma-visible">
      <tpl:apply/>
      <tpl:apply mode="row/remove"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="row/remove">
    <button type="button" class="right">
      <js:event name="click">
        %object%.remove();
      </js:event>
      <tpl:text>-</tpl:text>
    </button>
  </tpl:template>

</tpl:collection>
