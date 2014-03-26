<?xml version="1.0" encoding="utf-8"?>
<crud:crud
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

  <tpl:import>/#sylma/crud/list/root.tpl</tpl:import>

  <tpl:template match="sql:foreign">
    <tpl:apply select="ref()" mode="cell/content"/>
  </tpl:template>

  <tpl:template match="sql:datetime">
    <tpl:read select="format()"/>
  </tpl:template>

</crud:crud>
