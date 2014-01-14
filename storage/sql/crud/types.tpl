<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="sql:id" mode="label"/>
  <tpl:template match="sql:id" mode="input/empty">
    <tpl:register/>
    <tpl:apply mode="input/hidden/empty"/>
  </tpl:template>

  <tpl:template match="sql:id" mode="input">
    <tpl:register/>
    <tpl:apply mode="input/hidden"/>
  </tpl:template>

  <tpl:template match="sql:string-long" mode="input/empty" sql:ns="ns">
    <textarea id="form-{alias('form')}" name="{alias('form')}" class="field-input field-input-element"></textarea>
  </tpl:template>

  <tpl:template match="sql:string-long" mode="input/update" sql:ns="ns">
    <textarea id="form-{alias('form')}" name="{alias('form')}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </tpl:template>

  <tpl:template match="sql:boolean" mode="input/empty" sql:ns="ns">
    <tpl:apply mode="input/checkbox/empty"/>
  </tpl:template>

  <tpl:template match="sql:boolean" mode="input/update" sql:ns="ns">
    <tpl:apply mode="input/checkbox/update"/>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="input/empty" sql:ns="ns">

    <tpl:apply mode="date"/>

  </tpl:template>

</tpl:collection>
