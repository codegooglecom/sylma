<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>

  <tpl:template match="sql:foreign" mode="container">

    <tpl:if test="is-multiple()">
      <tpl:apply mode="container/multiple"/>
      <tpl:else>
        <tpl:apply mode="container/simple"/>
      </tpl:else>
    </tpl:if>

  </tpl:template>

  <tpl:template match="sql:foreign" mode="container/simple">

    <tpl:apply mode="container">
      <tpl:text tpl:name="type">foreign</tpl:text>
    </tpl:apply>

  </tpl:template>

  <tpl:template match="sql:foreign" mode="container/multiple">
    <fieldset class="field-container form-foreign" js:class="sylma.crud.Field">

      <js:include>/#sylma/crud/Field.js</js:include>

      <js:event name="change">
        %object%.downlight();
      </js:event>
      <js:name>
        <tpl:read select="alias('key')"/>
      </js:name>
      <tpl:apply mode="legend"/>
      <tpl:apply mode="input/boolean"/>
    </fieldset>
  </tpl:template>

  <tpl:template match="*" mode="legend">
    <legend>
      <tpl:read select="title()"/>
    </legend>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="input" xmode="insert">
    <tpl:apply mode="select-notest"/>
  </tpl:template>

  <view:template match="sql:foreign" mode="input" xmode="update">
    <tpl:apply mode="select-test"/>
  </view:template>

  <tpl:template match="sql:foreign" mode="input/boolean" xmode="insert">

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

  </tpl:template>

  <view:template match="sql:foreign" mode="input/boolean" xmode="update">

    <tpl:if test="is-multiple()">

      <tpl:variable name="values">
        <tpl:read select="values()"/>
      </tpl:variable>

      <tpl:apply mode="input/multiple/boolean/update">
        <tpl:read select="$values" tpl:name="values"/>
      </tpl:apply>
      <tpl:else>
        <tpl:apply mode="input/single/boolean/update"/>
      </tpl:else>
    </tpl:if>

    <tpl:apply mode="register"/>

  </view:template>

  <tpl:template match="*" mode="foreign/single/boolean/empty">

    <tpl:argument name="alias"/>
    <tpl:argument name="id" default="'{$alias}_{id}'"/>

    <div class="foreign-value">

      <tpl:apply mode="input/build">
        <tpl:read tpl:name="alias" select="'{$alias}'"/>
        <tpl:read tpl:name="id" select="$id"/>
        <tpl:read tpl:name="type" select="'radio'"/>
        <tpl:read tpl:name="value" select="id"/>
        <tpl:read tpl:name="class" select="'boolean'"/>
      </tpl:apply>

      <tpl:apply mode="foreign/label">
        <tpl:read tpl:name="alias" select="$id"/>
      </tpl:apply>

    </div>

  </tpl:template>

  <tpl:template match="*" mode="foreign/multiple/boolean/empty">

    <tpl:argument name="alias"/>

    <div class="foreign-value">

      <tpl:apply mode="input/build">
        <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
        <tpl:read tpl:name="type" select="'checkbox'"/>
        <tpl:read tpl:name="value" select="id"/>
        <tpl:read tpl:name="class" select="'boolean'"/>
      </tpl:apply>

      <tpl:apply mode="foreign/label">
        <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
      </tpl:apply>

    </div>

  </tpl:template>

  <tpl:template match="sql:foreign" mode="select-notest">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" class="field-input-element">
      <tpl:apply mode="input/foreign/events"/>
      <option value="0">
        <tpl:apply mode="input/foreign/default"/>
      </option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </tpl:template>

  <tpl:template match="*" mode="input/foreign/default">
    <tpl:text>&lt; Choose &gt;</tpl:text>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="select-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" class="field-input-element">
      <tpl:apply mode="input/foreign/events"/>
      <option value="0">
        <tpl:apply mode="input/foreign/default"/>
      </option>
      <tpl:apply select="all()" mode="select-option-test"/>
    </select>
  </tpl:template>

  <tpl:template match="*" mode="select-option-test">
    <option value="{id}">
      <tpl:if test="parent()/value() = id">
        <tpl:token name="selected">selected</tpl:token>
      </tpl:if>
      <tpl:apply mode="select-option-value" required="x"/>
    </option>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="select-multiple-notest">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}" multiple="multiple">
      <option value="0">
        <tpl:apply mode="input/foreign/default"/>
      </option>
      <tpl:apply select="all()" mode="select-option"/>
    </select>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="select-multiple-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <tpl:variable name="values">
      <tpl:apply select="values()"/>
    </tpl:variable>
    <select id="form-{$alias}" name="{$alias}" multiple="multiple">
      <option value="0">
        <tpl:apply mode="input/foreign/default"/>
      </option>
      <tpl:apply select="all()" mode="select-multiple-option-test">
        <tpl:read select="$values" tpl:name="values"/>
      </tpl:apply>
    </select>
  </tpl:template>

  <tpl:template match="*" mode="select-multiple-option-test">
    <tpl:argument name="values"/>
    <option value="{id}">
      <tpl:if test="id in $values">
        <tpl:token name="selected">selected</tpl:token>
      </tpl:if>
      <tpl:apply mode="select-option-value"/>
    </option>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="input/single/boolean/update">

    <tpl:apply select="all()" mode="foreign/single/boolean/update">
      <tpl:read tpl:name="value" select="$value"/>
      <tpl:read tpl:name="alias" select="alias('form')"/>
    </tpl:apply>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="input/multiple/boolean/update">

    <tpl:argument name="values"/>

    <tpl:apply select="all()" mode="foreign/multiple/boolean/update">
      <tpl:read tpl:name="values" select="$values"/>
      <tpl:read tpl:name="alias" select="alias('form')"/>
    </tpl:apply>
  </tpl:template>

  <tpl:template match="*" mode="foreign/single/boolean/update">

    <tpl:argument name="value"/>
    <tpl:argument name="alias"/>

    <tpl:apply mode="foreign/boolean/update">
      <tpl:read tpl:name="type" select="'radio'"/>
      <tpl:read tpl:name="value" select="id = $value"/>
      <tpl:read tpl:name="alias" select="{$alias}[]"/>
      <tpl:read tpl:name="id" select="'{$alias}_{id}'"/>
    </tpl:apply>

  </tpl:template>

  <tpl:template match="*" mode="foreign/multiple/boolean/update">

    <tpl:argument name="values"/>
    <tpl:argument name="alias"/>

    <tpl:apply mode="foreign/boolean/update">
      <tpl:read tpl:name="type" select="'checkbox'"/>
      <tpl:read tpl:name="value" select="(id in $values)"/>
      <tpl:read tpl:name="alias" select="'{$alias}[{id}]'"/>
    </tpl:apply>

  </tpl:template>

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

  <tpl:template match="*" mode="select-option">
    <option value="{id}">
      <tpl:apply mode="select-option-value" required="x"/>
    </option>
  </tpl:template>

  <tpl:template match="*" mode="select-test">
    <tpl:argument name="alias" default="alias('form')"/>
    <select id="form-{$alias}" name="{$alias}">
      <option value="0">
        <tpl:apply mode="input/foreign/default"/>
      </option>
      <tpl:apply select="all()" mode="select-option-test"/>
    </select>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="filter/content">
    <div class="filter" js:class="sylma.crud.list.Filter">
      <tpl:apply mode="input"/>
    </div>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="filter/text">
    <div class="filter" js:class="sylma.crud.list.Filter">
      <tpl:apply mode="input/build"/>
      <tpl:apply mode="input/clear"/>
    </div>
  </tpl:template>

</tpl:collection>
