<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"

  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:upl="http://2013.sylma.org/modules/uploader"
>

  <tpl:import>/#sylma/storage/sql/crud/table.tpl</tpl:import>
  <tpl:import>/#sylma/crud/move/move.tpl</tpl:import>

  <tpl:template match="*" mode="file/resources">

    <tpl:apply mode="file/resources"/>
    <tpl:apply mode="move/resources"/>

  </tpl:template>

  <tpl:template match="*" mode="file/init">

    <js:option name="position">
      <tpl:read select="position"/>
    </js:option>

  </tpl:template>

  <tpl:template match="*" mode="file/inputs">
    <tpl:argument name="position"/>
    <tpl:argument name="prefix"/>
    <tpl:apply mode="file/inputs">
      <tpl:read select="$prefix" tpl:name="prefix"/>
    </tpl:apply>
    <input js:node="position" type="hidden" name="{$prefix}[position]" value="{$position}"/>
  </tpl:template>

  <tpl:template match="*" mode="file/content">

    <tpl:apply mode="file/dropper"/>
    <tpl:apply mode="file/ref"/>
    <tpl:apply mode="move/scroller"/>

  </tpl:template>

  <tpl:template match="sql:table" mode="file/view">
    <sql:order>position</sql:order>
    <tpl:apply mode="file/view"/>
  </tpl:template>

  <tpl:template match="*" mode="file/actions/content">
    <tpl:apply mode="row/remove"/>
    <tpl:apply mode="row/move"/>
  </tpl:template>

</tpl:templates>
