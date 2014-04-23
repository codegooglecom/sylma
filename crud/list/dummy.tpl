<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:cls="http://2013.sylma.org/core/factory"
>

  <tpl:template mode="dummy">

    <tpl:apply select="source()"/>

    <tpl:variable name="token">
      <tpl:apply mode="dummy/path"/>
    </tpl:variable>

    <tpl:apply select="dummy()/setToken($token)"/>

  </tpl:template>

  <tpl:template match="*" mode="order/prepare">

    <tpl:apply select="dummy()/setDefault()">
      <tpl:read select="'sylma-order'"/>
      <tpl:read select="$$list-order"/>
    </tpl:apply>

  </tpl:template>

  <tpl:template mode="dummy/path">
    <le:path/>/list
  </tpl:template>

</tpl:collection>
