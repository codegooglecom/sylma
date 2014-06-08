<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="ssd:password" mode="input">

    <tpl:argument name="alias" default="alias()"/>

    <tpl:apply mode="input/build" xmode="insert">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'password'"/>
      <tpl:read tpl:name="value" select="''"/>
    </tpl:apply>

  </tpl:template>

</tpl:collection>
