<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:import>/#sylma/modules/datepicker/init.tpl</tpl:import>

  <tpl:template match="sql:datetime" mode="input">

    <tpl:apply mode="date"/>

  </tpl:template>

  <tpl:template match="*" mode="date">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="value" default="''"/>

    <tpl:apply mode="date/prepare"/>

    <div class="date-container" js:class="sylma.crud.Date">

      <tpl:apply mode="date/content">
        <tpl:read select="$alias" tpl:name="alias"/>
        <tpl:read select="$value" tpl:name="value"/>
      </tpl:apply>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="date/content">

    <tpl:argument name="alias"/>
    <tpl:argument name="value"/>

    <tpl:apply mode="input/build">
      <tpl:read tpl:name="alias" select="''"/>
      <tpl:read tpl:name="id" select="$alias"/>
      <tpl:read tpl:name="class" select="'date'"/>
      <tpl:read tpl:name="value" select="$value"/>
    </tpl:apply>
    <tpl:apply mode="input/build">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'hidden'"/>
      <tpl:read tpl:name="id" select="''"/>
    </tpl:apply>

  </tpl:template>

</tpl:collection>
