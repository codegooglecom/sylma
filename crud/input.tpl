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
    <tpl:apply mode="input/empty"/>
  </tpl:template>

  <tpl:template match="*" mode="input/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="type" default="'text'"/>

    <tpl:apply mode="input/empty/build">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="id" select="$id"/>
      <tpl:read tpl:name="type" select="$type"/>
    </tpl:apply>

  </tpl:template>

  <tpl:template match="*" mode="input/empty/build">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$id}" name="{$alias}"/>

  </tpl:template>

  <tpl:template match="*" mode="input/update">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <tpl:apply mode="input/update/build">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="id" select="$id"/>
      <tpl:read tpl:name="value" select="$value"/>
      <tpl:read tpl:name="type" select="$type"/>
    </tpl:apply>

  </tpl:template>

  <tpl:template match="*" mode="input/update/build">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$id}" value="{$value}" name="{$alias}">
      <tpl:apply mode="input/events"/>
    </input>

  </tpl:template>

  <tpl:template match="*" mode="input/hidden">
    <tpl:apply mode="input/hidden/empty"/>
  </tpl:template>

  <tpl:template match="*" mode="input/hidden/empty">
    <tpl:apply mode="input/empty/build">
      <tpl:read select="'hidden'" tpl:name="type"/>
    </tpl:apply>
  </tpl:template>


</tpl:collection>
