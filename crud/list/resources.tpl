<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:sql="http://2013.sylma.org/storage/sql"
>

  <tpl:template mode="list/resources">
    <tpl:apply mode="list/js"/>
    <tpl:apply mode="list/css"/>
    <tpl:apply mode="list/filters/js"/>
    <tpl:apply mode="list/filters/css"/>
  </tpl:template>

  <tpl:template mode="list/js">
    <js:include>../Form.js</js:include>
    <js:include>Table.js</js:include>
    <js:include>Head.js</js:include>
    <js:include>Row.js</js:include>
  </tpl:template>

  <tpl:template mode="list/css">
    <le:context name="css">
      <le:file>list.less</le:file>
    </le:context>
  </tpl:template>

  <tpl:template match="*" mode="list/filters/js">
    <js:include>Filters.js</js:include>
    <js:include>FilterContainer.js</js:include>
    <js:include>Filter.js</js:include>
  </tpl:template>

  <tpl:template match="*" mode="list/filters/css">
    <le:context name="css">
      <le:file>filters.less</le:file>
    </le:context>
  </tpl:template>

</tpl:collection>
