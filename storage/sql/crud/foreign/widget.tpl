<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:sql="http://2013.sylma.org/storage/sql"
>

  <tpl:template match="*" mode="foreign/widget/resources">

    <le:context name="css">
      <le:file>widget.less</le:file>
    </le:context>

    <js:include>/#sylma/ui/Clonable.js</js:include>
    <js:include>/#sylma/crud/Field.js</js:include>
    <js:include>Foreign.js</js:include>

  </tpl:template>

  <tpl:template match="sql:foreign" mode="foreign/widget">

    <div class="field-container widget-numeric clearfix" js:class="sylma.crud.Foreign" js:parent-name="select">

      <tpl:apply mode="foreign/widget/content"/>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="foreign/widget/content">

    <tpl:apply mode="label"/>

    <div class="select">

      <div class="list hidder" js:class="sylma.ui.Container" js:name="container">

        <tpl:apply mode="foreign/widget/empty"/>
        <tpl:apply select="all()" mode="foreign/widget/value"/>
      </div>

      <div class="input field">
        <div style="position: absolute; width: 0; overflow: hidden">
          <span js:node="focus" tabindex="-1">
            <js:event name="blur">
              %object%.hideContainer();
            </js:event>
          </span>
          <input name="{alias('form')}" type="hidden" js:node="input"/>
        </div>

        <span class="value" js:node="value">
          <js:event name="click">
            %object%.showContainer();
            %object%.getNode('focus').focus();
          </js:event>
          <tpl:apply mode="input/foreign/default"/>
        </span>

        <div class="scroller">
          <span class="up">
            <js:event name="click">
              %object%.stepValue(1);
            </js:event>
            <tpl:text>▲</tpl:text>
          </span>
          <span class="down">
            <js:event name="click">
              %object%.stepValue(-1);
            </js:event>
            <tpl:text>▼</tpl:text>
          </span>
        </div>
      </div>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="foreign/widget/value">

    <tpl:argument name="key" default="position()"/>
    <tpl:argument name="id" default="id"/>
    <tpl:argument name="content" default="nom"/>

    <span class="value" data-key="{$key}" data-id="{$id}">
      <js:event name="click">
        %object%.getParent('select').updateValue(this.get('data-key'), this.get('data-id'), this.get('html'));
      </js:event>
      <tpl:read select="$content"/>
    </span>

  </tpl:template>

  <tpl:template match="*" mode="foreign/widget/empty">

    <tpl:apply mode="foreign/widget/value">
      <tpl:read select="'0'" tpl:name="key"/>
      <tpl:read select="'0'" tpl:name="id"/>
      <tpl:apply mode="input/foreign/default" tpl:name="content"/>
    </tpl:apply>

  </tpl:template>

</tpl:collection>
