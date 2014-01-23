<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template mode="js">
    <js:include>Form.js</js:include>
    <js:include>Field.js</js:include>
    <js:include>type/Text.js</js:include>
  </tpl:template>

  <tpl:template match="*" mode="container/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="''"/>

    <div class="field clearfix field-{$type}" js:class="sylma.crud.Field">
      <js:event name="change">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="alias('key')"/>
      </js:name>
      <tpl:apply mode="register"/>
      <tpl:apply mode="label">
        <tpl:read tpl:name="alias" select="$alias"/>
        <tpl:read tpl:name="title" select="$title"/>
      </tpl:apply>
      <tpl:apply mode="input/empty">
        <tpl:read tpl:name="alias" select="$alias"/>
        <tpl:read tpl:name="type" select="$type"/>
      </tpl:apply>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="container">
    <tpl:apply mode="container/empty"/>
  </tpl:template>

  <tpl:template match="*" mode="register">
    <tpl:register/>
  </tpl:template>

  <tpl:template match="*" mode="label">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>

    <label for="form-{$alias}">
      <tpl:apply mode="label/value">
        <tpl:read select="$title" tpl:name="title"/>
      </tpl:apply>
      <tpl:apply mode="label/optional"/>
      <tpl:text> :</tpl:text>
    </label>

  </tpl:template>

  <tpl:template mode="label/optional">
    <tpl:if test="!is-optional()">
      <tpl:text>*</tpl:text>
    </tpl:if>
  </tpl:template>

  <tpl:template match="*" mode="label/value">
    <tpl:argument name="title" default="title()"/>
    <tpl:read select="$title"/>
  </tpl:template>


</tpl:collection>
