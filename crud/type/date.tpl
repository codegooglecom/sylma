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

  <tpl:template match="*" mode="date">

    <tpl:apply mode="date/prepare"/>

    <div js:class="sylma.crud.Date">

      <tpl:apply mode="date/content"/>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="date/content">

    <tpl:apply mode="input/update">
      <tpl:read tpl:name="alias" select="''"/>
      <tpl:read tpl:name="id" select="alias('form')"/>
    </tpl:apply>
    <tpl:apply mode="input/update">
      <tpl:read tpl:name="type" select="'hidden'"/>
      <tpl:read tpl:name="id" select="''"/>
    </tpl:apply>

  </tpl:template>


</tpl:collection>
