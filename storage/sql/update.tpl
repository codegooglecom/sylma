<?xml version="1.0" encoding="utf-8"?>
<view:view
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

  <view:template match="*" mode="container/update">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="value()"/>

    <div class="field clearfix" js:class="sylma.crud.Field">
      <js:event name="click">
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

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$alias}" value="{$value}" name="{$alias}"/>

  </view:template>

  <view:template match="sql:string-long" mode="input" sql:ns="ns">
    <textarea id="form-{alias()}" name="{alias()}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </view:template>

  <view:template match="sql:foreign" mode="container">

    <tpl:if test="is-multiple()">
      <tpl:apply mode="container">
        <tpl:text tpl:name="value">0</tpl:text>
      </tpl:apply>
      <tpl:else>
        <tpl:apply mode="container"/>
      </tpl:else>
    </tpl:if>

  </view:template>

  <view:template match="sql:foreign" mode="input" sql:ns="ns">
    <tpl:if test="is-multiple()">
      <tpl:apply mode="select-multiple-test"/>
      <tpl:else>
        <tpl:apply mode="select-test"/>
      </tpl:else>
    </tpl:if>
  </view:template>

  <view:template mode="form/token">
    <sql:token>
      <crud:path/>
      <tpl:text>/do</tpl:text>
    </sql:token>
  </view:template>

</view:view>
