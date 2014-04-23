<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
>

  <tpl:import>/#sylma/crud/update.tpl</tpl:import>

  <view:template match="sql:string-long" mode="input" sql:ns="ns">
    <tpl:apply mode="input/update"/>
  </view:template>

  <view:template match="sql:foreign" mode="container">

    <tpl:if test="is-multiple()">
      <tpl:apply mode="container/multiple/update"/>
      <tpl:else>
        <tpl:apply mode="container/update">
          <tpl:text tpl:name="type">foreign</tpl:text>
        </tpl:apply>
      </tpl:else>
    </tpl:if>

  </view:template>

  <tpl:template match="sql:foreign" mode="container/multiple/update">
    <fieldset class="field-container form-foreign" js:class="sylma.crud.Field">

      <js:include>/#sylma/crud/Field.js</js:include>

      <tpl:apply mode="reference/js"/>
      <js:event name="change">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="alias('key')"/>
      </js:name>
      <tpl:apply mode="legend"/>
      <tpl:apply mode="input/multiple/boolean/update"/>
    </fieldset>
  </tpl:template>

  <view:template match="sql:foreign" mode="input/update" sql:ns="ns">
    <tpl:apply mode="select-test"/>
  </view:template>

  <view:template match="sql:foreign" mode="input/boolean/update" sql:ns="ns">

    <tpl:if test="is-multiple()">
      <tpl:apply mode="input/multiple/boolean/update"/>
      <tpl:else>
        <tpl:apply mode="input/single/boolean/update"/>
      </tpl:else>
    </tpl:if>

    <tpl:apply mode="register"/>

  </view:template>

  <view:template match="sql:datetime" mode="input/update" sql:ns="ns">

    <tpl:apply mode="date"/>

  </view:template>

</view:view>
