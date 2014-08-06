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

  <tpl:template match="sql:id" mode="label"/>

  <tpl:template match="sql:id" mode="input">
    <tpl:register/>
    <tpl:apply mode="input/hidden"/>
  </tpl:template>

  <tpl:template match="sql:id" mode="input" xmode="insert">
    <tpl:register/>
    <tpl:apply mode="input/hidden"/>
  </tpl:template>

  <tpl:template match="sql:id" mode="post">
    <sql:filter>
      <le:get-argument name="id" source="post"/>
    </sql:filter>
  </tpl:template>

  <tpl:template match="sql:string-long" mode="input">
    <textarea id="form-{alias('form')}" name="{alias('form')}" class="field field-text field-input-element">
      <tpl:apply mode="input/value"/>
    </textarea>
  </tpl:template>

  <tpl:template match="sql:boolean" mode="input" xmode="insert">
    <tpl:apply mode="input/checkbox/empty"/>
  </tpl:template>

  <tpl:template match="sql:boolean" mode="input" xmode="update">
    <tpl:apply mode="input/checkbox/update"/>
  </tpl:template>

</tpl:collection>
