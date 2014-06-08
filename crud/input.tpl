<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="*" mode="input">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="type" default="'text'"/>

    <tpl:apply mode="input/build">
      <tpl:read select="$alias" tpl:name="alias"/>
      <tpl:read select="$type" tpl:name="type"/>
    </tpl:apply>

  </tpl:template>

  <tpl:template match="*" mode="input/build">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="class" default="'text'"/>
    <tpl:argument name="value" default="''"/>

    <tpl:variable name="final-value">
      <tpl:apply mode="input/value"/>
      <tpl:read select="$value"/>
    </tpl:variable>

    <input class="field field-{$class}" type="{$type}" id="form-{$id}" value="{$final-value}" name="{$alias}">
      <tpl:apply mode="input/events"/>
    </input>

  </tpl:template>

  <tpl:template match="*" mode="input/value" xmode="insert"/>

  <tpl:template match="*" mode="input/value" xmode="update">
    <tpl:read select="value()"/>
  </tpl:template>

  <tpl:template match="*" mode="input/hidden">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>

    <tpl:apply mode="input/build">
      <tpl:read select="'hidden'" tpl:name="type"/>
      <tpl:read select="$alias" tpl:name="alias"/>
    </tpl:apply>
  </tpl:template>

</tpl:collection>
