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

  <tpl:template match="sql:datetime" mode="container">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'date'"/>
    <tpl:argument name="object" default="alias('key')"/>

    <js:include>/#sylma/ui/Clonable.js</js:include>
    <js:include>/#sylma/crud/Field.js</js:include>

    <div class="field-container clearfix field-{$type} {$object}" js:class="sylma.crud.Date">

      <js:event name="change">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="$object"/>
      </js:name>
      <tpl:apply mode="register"/>
      <tpl:apply mode="label">
        <tpl:read tpl:name="alias" select="$alias"/>
        <tpl:read tpl:name="title" select="$title"/>
      </tpl:apply>
      <tpl:apply mode="date">
        <tpl:read tpl:name="alias" select="$alias"/>
      </tpl:apply>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="date">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="value" default="''"/>

    <tpl:apply mode="date/prepare"/>

    <tpl:apply mode="date/content">
      <tpl:read select="$alias" tpl:name="alias"/>
      <tpl:read select="$value" tpl:name="value"/>
    </tpl:apply>

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
