<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:cls="http://2013.sylma.org/core/factory"
>

  <tpl:import>../input.tpl</tpl:import>
  <tpl:import>../type/numeric.tpl</tpl:import>
  <tpl:import>../type/date.tpl</tpl:import>
  <tpl:import>../update.tpl</tpl:import>

  <tpl:template match="*" mode="list/filters/js">
    <js:include>Filters.js</js:include>
    <js:include>FilterContainer.js</js:include>
    <js:include>Filter.js</js:include>
  </tpl:template>

  <tpl:template match="*" mode="list/filters/css">
    <le:context name="css">
      <le:file>filters.less</le:file>
    </le:context>
  </tpl:template>

  <tpl:template match="*" mode="filters">

    <tpl:apply mode="list/filters/js"/>
    <tpl:apply mode="list/filters/css"/>

    <div class="filters hidder clearfix" js:name="filters" js:parent-name="filters" js:class="sylma.crud.list.Filters">
      <div class="filter-container title" js:class="sylma.crud.list.FilterContainer">
        <tpl:apply mode="filters/corner"/>
      </div>
      <tpl:apply use="list-cols" mode="filter"/>
    </div>

  </tpl:template>
<!--
  <tpl:template match="*" mode="filters/corner">
    <a>filters</a>
  </tpl:template>
-->
  <tpl:template match="*" mode="filter">
    <div class="filter-container" js:class="sylma.crud.list.FilterContainer">
      <tpl:apply mode="filter/content"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="filter/content">
    <div class="filter" js:class="sylma.crud.list.Filter">
      <tpl:apply mode="input/update"/>
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

  <tpl:template match="sql:string" mode="filter/internal">
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
