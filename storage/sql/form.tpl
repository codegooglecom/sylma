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

  <view:template>
    <form js:class="sylma.crud.Form" class="sylma-form">
      <js:include>/#sylma/template/crud.js</js:include>
      <tpl:apply mode="init"/>
      <tpl:apply use="form-cols" mode="container"/>
      <tpl:apply mode="action"/>
      <input type="submit" value="Envoyer"/>
      <tpl:apply mode="form/token"/>
    </form>
  </view:template>

  <view:template mode="action"/>

  <view:template match="*" mode="_tmp">

    <tpl:argument name="alias" default="alias()"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="value()"/>

    <tpl:apply mode="container">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="title" select="$title"/>
      <tpl:read tpl:name="type" select="$type"/>
      <tpl:read tpl:name="value" select="$value"/>
    </tpl:apply>

  </view:template>

  <view:template match="*" mode="container">

    <tpl:argument name="alias" default="alias()"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="''"/>

    <div class="field clearfix" js:class="sylma.crud.Field">
      <js:event name="click">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="alias()"/>
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

  <view:template match="*" mode="register">
    <tpl:register/>
  </view:template>

  <view:template match="*" mode="label">

    <tpl:argument name="alias" default="alias()"/>
    <tpl:argument name="title" default="title()"/>

    <label for="form-{$alias}"><tpl:read select="$title"/> :</label>

  </view:template>

  <view:template match="*" mode="input">

    <tpl:argument name="alias" default="alias()"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$alias}" value="{$value}" name="{$alias}"/>

  </view:template>

  <view:template match="sql:string-long" mode="input" sql:ns="ns">
    <textarea id="form-{alias()}" name="{alias()}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </view:template>

  <view:template match="sql:foreign" mode="input" sql:ns="ns">
    <tpl:if test="is-multiple()">
      <tpl:apply mode="select-multiple-notest"/>
      <tpl:else>
        <tpl:apply mode="select-notest"/>
      </tpl:else>
    </tpl:if>
  </view:template>

  <view:template match="*" mode="select-notest">
    <select id="form-{alias()}" name="{alias()}">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-test">
    <select id="form-{alias()}" name="{alias()}">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option-test"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-option-test">
    <option value="{id}">
      <tpl:if test="parent()/value() = id">
        <tpl:token name="selected">selected</tpl:token>
      </tpl:if>
      <tpl:apply mode="select-option-value"/>
    </option>
  </view:template>

  <view:template match="*" mode="select-multiple-notest">
    <select id="form-{alias()}" name="{alias()}" multiple="multiple">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-multiple-test">
    <tpl:variable name="values">
      <tpl:read select="values()"/>
    </tpl:variable>
    <select id="form-{alias()}" name="{alias()}" multiple="multiple">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-multiple-option-test">
        <tpl:read select="$values" tpl:name="values"/>
      </tpl:apply>
    </select>
  </view:template>

  <view:template match="*" mode="select-multiple-option-test">
    <tpl:argument name="values"/>
    <option value="{id}">
      <tpl:if test="id in $values">
        <tpl:token name="selected">selected</tpl:token>
      </tpl:if>
      <tpl:apply mode="select-option-value"/>
    </option>
  </view:template>

  <view:template match="*" mode="select-option">
    <option value="{id}">
      <tpl:apply mode="select-option-value"/>
    </option>
  </view:template>

  <view:template match="ssd:password" mode="input" ssd:ns="ns">

    <tpl:argument name="alias" default="alias()"/>

    <tpl:apply mode="input">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'password'"/>
      <tpl:read tpl:name="value" select="''"/>
    </tpl:apply>

  </view:template>

</view:view>
