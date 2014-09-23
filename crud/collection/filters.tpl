<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
  xmlns:cls="http://2013.sylma.org/core/factory"

  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"


>

  <tpl:import>../input.tpl</tpl:import>
  <tpl:import>../type/numeric.tpl</tpl:import>
  <tpl:import>../type/date.tpl</tpl:import>

  <tpl:template match="*" mode="filters">

    <tpl:apply mode="list/filters/js"/>
    <tpl:apply mode="list/filters/css"/>

    <div class="filters hidder clearfix" js:name="filters" js:parent-name="filters" js:class="sylma.crud.collection.Filters">
      <div class="filter-container title" js:class="sylma.crud.collection.FilterContainer">
        <tpl:apply mode="filters/corner"/>
      </div>
      <tpl:apply mode="filters/content"/>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="filters/content">
    <tpl:apply use="list-cols" mode="filter" xmode="update"/>
  </tpl:template>
<!--
  <tpl:template match="*" mode="filters/corner"> </tpl:template>
-->
  <tpl:template match="*" mode="filter">
    <div class="filter-container" js:class="sylma.crud.collection.FilterContainer">
      <tpl:apply mode="filter/content"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="filter/content">
    <div class="filter" js:class="sylma.crud.collection.Filter">
      <tpl:register/>
      <tpl:apply mode="input"/>
      <tpl:apply mode="input/clear"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="input/clear">
    <span class="close">
      <js:event name="click">
        %object%.clear();
      </js:event>
      <tpl:text>✕</tpl:text>
    </span>
  </tpl:template>

  <tpl:template match="*" mode="input/events">
    <js:event name="input">
      %object%.update();
    </js:event>
  </tpl:template>

  <tpl:template match="*" mode="filter/internal">
    <sql:filter optional="x">
      <tpl:read/>
    </sql:filter>
  </tpl:template>

  <tpl:template match="xs:string" mode="filter/internal">
    <tpl:argument name="key" default="alias()"/>
    <tpl:apply mode="filter/internal/text">
      <tpl:read select="$key" tpl:name="key"/>
    </tpl:apply>
  </tpl:template>

  <tpl:template match="*" mode="filter/internal/text">
    <tpl:argument name="key"/>
    <sql:filter optional="x" op="search">
      <tpl:read/>
    </sql:filter>
  </tpl:template>

</tpl:collection>
