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
      <tpl:apply mode="form"/>
      <tpl:apply mode="form/action"/>
      <tpl:apply mode="form/token"/>
    </form>
  </view:template>

  <tpl:template mode="form">
    <tpl:apply use="form-cols" mode="container"/>
  </tpl:template>

  <view:template mode="form/action">
    <input type="submit" value="Envoyer"/>
  </view:template>

  <view:template match="*" mode="container/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="title" default="title()"/>
    <tpl:argument name="type" default="'text'"/>
    <tpl:argument name="value" default="''"/>

    <div class="field clearfix field-{$type}" js:class="sylma.crud.Field">
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

    <label for="form-{$alias}">
      <tpl:apply mode="label/value"/>
      <tpl:if test="!is-optional()">
        <tpl:text>*</tpl:text>
      </tpl:if>
      <tpl:text> :</tpl:text>
    </label>

  </view:template>

  <tpl:template match="*" mode="label/value">
    <tpl:read select="title()"/>
  </tpl:template>

  <view:template match="*" mode="input">
    <tpl:apply mode="input/empty"/>
  </view:template>

  <view:template match="*" mode="input/empty">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$id}" name="{$alias}"/>

  </view:template>

  <view:template match="*" mode="input/update">

    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:argument name="id" default="$alias"/>
    <tpl:argument name="value" default="value()"/>
    <tpl:argument name="type" default="'text'"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$id}" value="{$value}" name="{$alias}"/>

  </view:template>

  <view:template match="sql:string-long" mode="input/empty" sql:ns="ns">
    <textarea id="form-{alias('form')}" name="{alias('form')}" class="field-input field-input-element"></textarea>
  </view:template>

  <view:template match="sql:string-long" mode="input/update" sql:ns="ns">
    <textarea id="form-{alias('form')}" name="{alias('form')}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </view:template>

  <view:template match="sql:foreign" mode="container">

    <tpl:if test="is-multiple()">
      <tpl:apply mode="container/multiple/empty"/>
      <tpl:else>
        <tpl:apply mode="container/empty"/>
      </tpl:else>
    </tpl:if>

  </view:template>

  <view:template match="sql:foreign" mode="container/empty">

    <tpl:apply mode="container/empty">
      <tpl:text tpl:name="type">foreign</tpl:text>
    </tpl:apply>

  </view:template>

  <tpl:template match="sql:foreign" mode="container/multiple/empty">
    <fieldset class="field form-foreign" js:class="sylma.crud.Field">
      <js:event name="click">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="alias('key')"/>
      </js:name>
      <tpl:apply mode="legend"/>
      <tpl:apply mode="input/boolean/empty"/>
    </fieldset>
  </tpl:template>

  <tpl:template match="*" mode="legend">
    <legend>
      <tpl:read select="title()"/>
    </legend>
  </tpl:template>

  <view:template match="sql:foreign" mode="input/empty" sql:ns="ns">
    <tpl:apply mode="select-notest"/>
  </view:template>
<!--
  <view:template match="sql:foreign" mode="input/empty" sql:ns="ns">
    <tpl:apply mode="input/boolean/empty"/>
  </view:template>
-->
  <view:template match="sql:foreign" mode="input/boolean/empty">

    <tpl:if test="is-multiple()">
      <tpl:apply select="all()" mode="foreign/multiple/boolean/empty">
        <tpl:read tpl:name="alias" select="alias('form')"/>
      </tpl:apply>
      <tpl:else>
        <tpl:apply select="all()" mode="foreign/single/boolean/empty">
          <tpl:read tpl:name="alias" select="alias('form')"/>
        </tpl:apply>
      </tpl:else>
    </tpl:if>

    <tpl:apply mode="register"/>

  </view:template>

  <view:template match="*" mode="foreign/single/boolean/empty">

    <tpl:argument name="alias"/>
    <tpl:argument name="id" default="'{$alias}_{id}'"/>

    <div class="foreign-value">

      <tpl:apply mode="input/update">
        <tpl:read tpl:name="alias" select="'{$alias}'"/>
        <tpl:read tpl:name="id" select="$id"/>
        <tpl:read tpl:name="type" select="'radio'"/>
        <tpl:read tpl:name="value" select="id"/>
      </tpl:apply>

      <tpl:apply mode="foreign/label">
        <tpl:read tpl:name="alias" select="$id"/>
      </tpl:apply>

    </div>

  </view:template>

  <view:template match="*" mode="foreign/multiple/boolean/empty">

    <tpl:argument name="alias"/>

    <div class="foreign-value">

      <tpl:apply mode="input/update">
        <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
        <tpl:read tpl:name="type" select="'checkbox'"/>
        <tpl:read tpl:name="value" select="id"/>
      </tpl:apply>

      <tpl:apply mode="foreign/label">
        <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
      </tpl:apply>

    </div>

  </view:template>

  <view:template match="sql:foreign" mode="select-notest">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" class="field-input-element">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="sql:foreign" mode="select-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" class="field-input-element">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option-test"/>
    </select>
  </view:template>

  <view:template match="*" mode="select-option-test">
    <option value="{id}">
      <tpl:if test="parent()/value() = id">
        <tpl:token name="selected">selected</tpl:token>
      </tpl:if>
      <tpl:apply mode="select-option-value" required="x"/>
    </option>
  </view:template>

  <view:template match="sql:foreign" mode="select-multiple-notest">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" multiple="multiple">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </view:template>

  <view:template match="sql:foreign" mode="select-multiple-test">
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

  <view:template match="sql:foreign" mode="input/single/boolean/update">

    <tpl:apply select="all()" mode="foreign/single/boolean/update">
      <tpl:read tpl:name="value" select="$value"/>
      <tpl:read tpl:name="alias" select="alias('form')"/>
    </tpl:apply>
  </view:template>

  <view:template match="sql:foreign" mode="input/multiple/boolean/update">

    <tpl:variable name="values">
      <tpl:read select="values()"/>
    </tpl:variable>

    <tpl:apply select="all()" mode="foreign/multiple/boolean/update">
      <tpl:read tpl:name="values" select="$values"/>
      <tpl:read tpl:name="alias" select="alias('form')"/>
    </tpl:apply>
  </view:template>

  <view:template match="*" mode="foreign/single/boolean/update">

    <tpl:argument name="value"/>
    <tpl:argument name="alias"/>

    <tpl:apply mode="foreign/boolean/update">
      <tpl:read tpl:name="type" select="'radio'"/>
      <tpl:read tpl:name="value" select="id = $value"/>
      <tpl:read tpl:name="alias" select="{$alias}[]"/>
      <tpl:read tpl:name="id" select="'{$alias}_{id}'"/>
    </tpl:apply>

  </view:template>

  <view:template match="*" mode="foreign/multiple/boolean/update">

    <tpl:argument name="values"/>
    <tpl:argument name="alias"/>

    <tpl:apply mode="foreign/boolean/update">
      <tpl:read tpl:name="type" select="'checkbox'"/>
      <tpl:read tpl:name="value" select="(id in $values)"/>
      <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
    </tpl:apply>

  </view:template>

  <tpl:template match="*" mode="foreign/boolean/update">

    <tpl:argument name="type"/>
    <tpl:argument name="value"/>
    <tpl:argument name="alias"/>
    <tpl:argument name="id" default="$alias"/>

    <div class="foreign-value">

      <tpl:apply mode="input/boolean">
        <tpl:read tpl:name="type" select="$type"/>
        <tpl:read tpl:name="value" select="$value"/>
        <tpl:read tpl:name="content" select="id"/>
        <tpl:read tpl:name="id" select="$id"/>
        <tpl:read tpl:name="alias" select="$alias"/>
      </tpl:apply>

      <tpl:apply mode="foreign/label">
        <tpl:read tpl:name="alias" select="$id"/>
      </tpl:apply>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="foreign/label">

    <tpl:argument name="alias"/>

    <label for="form-{$alias}">
      <tpl:apply mode="select-option-value" required="x"/>
    </label>

  </tpl:template>

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
      <tpl:apply mode="legend"/>
      <tpl:apply mode="template/add"/>
      <tpl:apply mode="template"/>
      <div js:node="content">
        <tpl:apply select="ref()" mode="update"/>
      </div>
    </fieldset>
  </view:template>

  <tpl:template match="sql:reference" mode="template/add">
    <button type="button" js:class="sylma.ui.Base">
      <js:event name="click">
        %parent%.addTemplate();
      </js:event>
      <tpl:text>+</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="sql:reference" mode="template">
    <div js:name="template" js:class="sylma.crud.fieldset.Template" class="form-reference clearfix sylma-hidder" style="display: none">
      <tpl:apply select="static()" mode="empty"/>
      <button type="button" class="right">
        <js:event name="click">
          %object%.remove();
        </js:event>
        <tpl:text>-</tpl:text>
      </button>
    </div>
  </tpl:template>

  <view:template match="*" mode="select-option">
    <option value="{id}">
      <tpl:apply mode="select-option-value"/>
    </option>
  </view:template>

  <view:template match="*" mode="select-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select-option-test"/>
    </select>
  </view:template>

  <tpl:template match="*" mode="input/boolean">

    <tpl:argument name="type" default="'radio'"/>
    <tpl:argument name="value" default="''"/>
    <tpl:argument name="content" default="'1'"/>
    <tpl:argument name="alias" default="alias()"/>
    <tpl:argument name="id" default="$alias"/>

    <input class="field-input field-input-element" type="{$type}" id="form-{$id}" value="{$content}" name="{$alias}">
      <tpl:apply mode="input/boolean/value">
        <tpl:read select="$value" tpl:name="value"/>
      </tpl:apply>
    </input>

  </tpl:template>

  <tpl:template match="*" mode="input/boolean/value">

    <tpl:argument name="value" default="''"/>

    <tpl:if test="$value">
      <tpl:token name="checked">checked</tpl:token>
    </tpl:if>

  </tpl:template>

  <tpl:template match="*" mode="input/checkbox/empty">

    <tpl:apply mode="input/update">
      <tpl:read tpl:name="type" select="'checkbox'"/>
      <tpl:read tpl:name="value" select="'1'"/>
    </tpl:apply>
    <!--<input class="field-input field-input-element" type="checkbox" id="form-{$alias}" value="1" name="{$alias}"/>-->
  </tpl:template>

  <tpl:template match="*" mode="input/checkbox/update">

    <tpl:apply mode="input/boolean">
      <tpl:read tpl:name="type" select="'checkbox'"/>
      <tpl:read tpl:name="value" select="value()"/>
      <tpl:read tpl:name="content" select="'1'"/>
    </tpl:apply>

  </tpl:template>

  <view:template match="ssd:password" mode="input" ssd:ns="ns">
    <tpl:apply mode="input/empty"/>
  </view:template>

  <view:template match="ssd:password" mode="input/empty" ssd:ns="ns">

    <tpl:argument name="alias" default="alias()"/>

    <tpl:apply mode="input/boolean">
      <tpl:read tpl:name="alias" select="$alias"/>
      <tpl:read tpl:name="type" select="'password'"/>
      <tpl:read tpl:name="content" select="''"/>
    </tpl:apply>

  </view:template>

  <view:template match="sql:boolean" mode="input/empty" sql:ns="ns">
    <tpl:apply mode="input/checkbox/empty"/>
  </view:template>

  <view:template match="sql:boolean" mode="input/update" sql:ns="ns">
    <tpl:apply mode="input/checkbox/update"/>
  </view:template>

</view:view>
