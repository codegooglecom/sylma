<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <view:template match="*" mode="container/update">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="value()"/>

    <div class="field clearfix field-{$type}" js:class="sylma.crud.Field">

      <js:include>/#sylma/crud/Field.js</js:include>

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
      <tpl:apply mode="input">
        <tpl:read tpl:name="alias" select="$alias"/>
        <tpl:read tpl:name="type" select="$type"/>
        <tpl:read tpl:name="value" select="$value"/>
      </tpl:apply>
    </div>

  </view:template>

  <view:template match="*" mode="container">
    <tpl:apply mode="container/update"/>
  </view:template>

  <view:template match="*" mode="input">
    <tpl:apply mode="input/update"/>
  </view:template>

  <view:template match="*" mode="input/hidden">
    <tpl:apply mode="input/update/build">
      <tpl:read select="'hidden'" tpl:name="type"/>
    </tpl:apply>
  </view:template>

  <tpl:template match="*" mode="date/content">

    <tpl:apply mode="input/update">
      <tpl:read tpl:name="alias" select="''"/>
      <tpl:read tpl:name="id" select="alias('form')"/>
      <tpl:read tpl:name="class" select="'date'"/>
    </tpl:apply>
    <tpl:apply mode="input/update">
      <tpl:read tpl:name="type" select="'hidden'"/>
      <tpl:read tpl:name="id" select="''"/>
    </tpl:apply>

  </tpl:template>

</tpl:collection>
