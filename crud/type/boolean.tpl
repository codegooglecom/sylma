<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
>


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

</tpl:collection>
