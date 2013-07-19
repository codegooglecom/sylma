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

  <view:template match="*" mode="container/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="''"/>

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
      <tpl:apply mode="input/empty">
        <tpl:read tpl:name="alias" select="$alias"/>
        <tpl:read tpl:name="type" select="$type"/>
      </tpl:apply>
    </div>

  </view:template>

  <view:template match="*" mode="container">
    <tpl:apply mode="container/empty"/>
  </view:template>

  <view:template match="*" mode="register">
    <tpl:register/>
  </view:template>

  <view:template match="*" mode="label">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>

    <label for="form-{$alias}">
      <tpl:read select="$title"/>
      <tpl:if test="!is-optional()">
        <tpl:text>*</tpl:text>
      </tpl:if>
      <tpl:text> :</tpl:text>
    </label>

  </view:template>

  <view:template match="*" mode="input">
    <tpl:apply mode="input/empty"/>
  </view:template>

  <view:template match="*" mode="input/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$alias}" name="{$alias}"/>

  </view:template>

  <view:template match="*" mode="input/update">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$alias}" value="{$value}" name="{$alias}"/>

  </view:template>

  <view:template match="sql:string-long" mode="input/empty" sql:ns="ns">
    <textarea id="form-{alias()}" name="{alias()}" class="field-input field-input-element"></textarea>
  </view:template>

  <view:template match="sql:string-long" mode="input/update" sql:ns="ns">
    <textarea id="form-{alias()}" name="{alias()}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </view:template>

  <view:template match="sql:foreign" mode="input/empty" sql:ns="ns">
    <tpl:if test="is-multiple()">
      <tpl:apply mode="select-multiple-notest"/>
      <tpl:else>
        <tpl:apply mode="select-notest"/>
      </tpl:else>
    </tpl:if>
  </view:template>

  <view:template match="sql:table" sql:ns="ns" mode="empty">
    <tpl:apply select="* ^ sql:foreign" mode="container/empty"/>
  </view:template>

  <view:template match="sql:table" sql:ns="ns">
    <tpl:apply select="* ^ sql:foreign" mode="container"/>
  </view:template>

  <view:template match="sql:table" sql:ns="ns" mode="update">
    <div js:class="sylma.crud.fieldset.Row" class="form-reference clearfix">
      <tpl:apply/>
      <button type="button" class="right">
        <js:event name="click">
          %object%.remove();
        </js:event>
        <tpl:text>-</tpl:text>
      </button>
    </div>
  </view:template>

  <view:template match="sql:reference" mode="container" sql:ns="ns">
    <fieldset js:class="sylma.crud.fieldset.Container">
      <legend>
        <tpl:read select="title()"/>
      </legend>
      <button type="button">
        <js:event name="click">
          %object%.addTemplate();
        </js:event>
        <tpl:text>+</tpl:text>
      </button>
      <div js:name="template" js:class="sylma.crud.fieldset.Template" class="form-reference clearfix sylma-hidder" style="display: none">
        <tpl:apply select="static()" mode="empty"/>
        <button type="button" class="right">
          <js:event name="click">
            %object%.remove();
          </js:event>
          <tpl:text>-</tpl:text>
        </button>
      </div>
      <div js:node="content">
        <tpl:apply select="ref()" mode="update"/>
      </div>
    </fieldset>
  </view:template>

  <view:template match="*" mode="select-notest">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}">
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
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" multiple="multiple">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-multiple-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:variable name="values">
      <tpl:read select="values()"/>
    </tpl:variable>
    <select id="form-{$alias}" name="{$alias}" multiple="multiple">
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
    <tpl:apply mode="input/empty"/>
  </view:template>

  <view:template match="ssd:password" mode="input/empty" ssd:ns="ns">

    <tpl:argument name="alias" default="alias()"/>

    <tpl:apply mode="input/empty">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'password'"/>
    </tpl:apply>

  </view:template>

  <view:template match="sql:boolean" mode="input/empty" sql:ns="ns">

    <tpl:argument name="value" default="''"/>
    <tpl:argument name="alias" default="alias()"/>

    <input class="field-input field-input-element" type="checkbox" id="form-{$alias}" value="1" name="{$alias}">
      <tpl:apply mode="input/value">
        <tpl:read select="$value" tpl:name="value"/>
      </tpl:apply>
    </input>

  </view:template>

  <tpl:template match="sql:boolean" mode="input/value">

    <tpl:argument name="value" default="''"/>

    <tpl:if test="$value">
      <tpl:token name="checked">checked</tpl:token>
    </tpl:if>

  </tpl:template>

  <view:template match="sql:boolean" mode="input/update" sql:ns="ns">

    <tpl:argument name="alias" default="alias()"/>

    <tpl:apply mode="input/empty">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'checkbox'"/>
    </tpl:apply>

  </view:template>

</view:view>
