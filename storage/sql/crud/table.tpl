<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
>

  <tpl:template match="sql:table" mode="form/build">

    <tpl:variable name="action">
      <tpl:apply mode="init/action"/>
    </tpl:variable>

    <form js:class="sylma.crud.Form" class="sylma-form" action="{$action}" method="post" js:name="form" js:parent-name="form">

      <tpl:apply mode="form/content"/>

    </form>

  </tpl:template>

  <tpl:template match="*" mode="form/ajax">

    <tpl:variable name="action">
      <tpl:apply mode="init/action"/>
    </tpl:variable>

    <form class="sylma-form" action="{$action}" js:parent-name="form" method="post" js:class="sylma.crud.FormAjax">

      <js:include>/#sylma/crud/FormAjax.js</js:include>

      <js:name>
        <tpl:read select="name()"/>
      </js:name>
      <js:option name="ajax">1</js:option>

      <tpl:apply mode="form/init"/>
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

  <tpl:template match="sql:table" mode="form/content" xmode="update">

    <js:option name="delete" cast="x">
      <tpl:apply mode="init/delete"/>
    </js:option>

    <tpl:apply mode="form/content"/>

  </tpl:template>

  <tpl:template match="sql:table" mode="form/content">

    <tpl:apply mode="css"/>

    <js:include>/#sylma/crud/Form.js</js:include>

    <tpl:apply mode="form/init"/>
    <tpl:apply mode="form"/>

    <tpl:apply mode="form/actions"/>
    <tpl:apply mode="form/token"/>

  </tpl:template>

  <tpl:template match="sql:table" mode="css">

    <le:context name="css">
      <le:file>/#sylma/modules/html/medias/form.less</le:file>
    </le:context>

  </tpl:template>

  <tpl:template match="sql:table" mode="form">
    <tpl:apply use="form-cols" mode="container"/>
  </tpl:template>

  <tpl:template match="*" mode="form/actions">
    <!-- @deprecate class=form-actions -->
    <div class="form-actions actions">
      <tpl:apply mode="form/actions/content"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="form/actions/content" xmode="insert">
    <tpl:apply mode="form/cancel"/>
    <tpl:apply mode="form/save"/>
  </tpl:template>

  <tpl:template match="*" mode="form/actions/content" xmode="update">
    <tpl:apply mode="form/cancel"/>
    <tpl:apply mode="form/delete"/>
    <tpl:apply mode="form/save"/>
  </tpl:template>

  <tpl:template match="*" mode="form/save">
    <button class="save">
      <tpl:apply mode="form/save/content"/>
    </button>
  </tpl:template>

  <tpl:template match="*" mode="form/delete" xmode="update">
    <tpl:apply mode="form/delete/container"/>
  </tpl:template>

  <tpl:template match="*" mode="form/delete/container">
    <div class="delete">
      <button type="button">
        <js:event name="click">
          %object%.deleteItem();
        </js:event>
        <tpl:apply mode="form/delete/content"/>
      </button>
      <div class="hidder" js:node="delete">
        <span>?</span>
        <button type="button" class="yes">
          <js:event name="click">
            %object%.deleteConfirm();
          </js:event>
          <tpl:text>yes</tpl:text>
        </button>
        <button type="button" class="no">
          <js:event name="click">
            %object%.deleteCancel();
          </js:event>
          <tpl:text>no</tpl:text>
        </button>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="form/cancel">
    <button type="button" class="cancel">
      <js:event name="click">
        %object%.cancel();
      </js:event>
      <tpl:apply mode="form/cancel/content"/>
    </button>
  </tpl:template>

  <tpl:template match="*" mode="form/save/content">save</tpl:template>
  <tpl:template match="*" mode="form/delete/content">delete</tpl:template>
  <tpl:template match="*" mode="form/cancel/content">cancel</tpl:template>

  <tpl:template match="sql:table">
    <tpl:apply select="* ^ sql:foreign" mode="container"/>
  </tpl:template>

  <tpl:template match="sql:table" mode="fieldset">
    <div js:class="sylma.crud.fieldset.Row" class="form-reference clearfix sylma-hidder sylma-visible">
      <tpl:apply mode="fieldset/content"/>
      <tpl:apply mode="row/remove"/>
      <tpl:apply mode="fieldset/register"/>
    </div>
  </tpl:template>

  <tpl:template match="sql:table" mode="fieldset/register">
    <tpl:apply select="* ^ sql:foreign" mode="register"/>
  </tpl:template>

  <tpl:template match="*" mode="fieldset/legend">
    <legend>
      <tpl:read select="title()"/>
    </legend>
  </tpl:template>

  <tpl:template match="*" mode="row/remove">
    <button type="button" class="right">
      <js:event name="click" arguments="e">
        e.stopPropagation();
        %object%.remove();
      </js:event>
      <tpl:text>-</tpl:text>
    </button>
  </tpl:template>

</tpl:collection>
