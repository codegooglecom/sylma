<?xml version="1.0" encoding="utf-8"?>
<crud:crud
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

  <tpl:import>/#sylma/crud/list/root.tpl</tpl:import>
  <tpl:import>common.tpl</tpl:import>
  <tpl:import>foreign.tpl</tpl:import>

  <tpl:template match="sql:foreign" mode="input/foreign/events">
    <js:event name="change">
      %object%.update();
    </js:event>
  </tpl:template>

  <tpl:template match="sql:foreign" mode="input/default">
    <tpl:text>&lt; All &gt;</tpl:text>
  </tpl:template>

  <tpl:template match="sql:foreign">
    <tpl:apply select="ref()" mode="cell/content"/>
  </tpl:template>

  <tpl:template match="sql:datetime">
    <tpl:read select="format()"/>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="filter">
    <tpl:argument name="alias" default="alias('form')"/>
    <div class="filter-container"  js:class="sylma.crud.list.FilterContainer">
      <div class="filter" js:class="sylma.crud.list.Filter">
        <tpl:apply mode="date">
          <tpl:read select="'{$alias}_from'" tpl:name="alias"/>
          <tpl:read select="parent()/collection()/dummy()/default()" tpl:name="value">
            <tpl:read select="'{$alias}_from'"/>
          </tpl:read>
        </tpl:apply>
       <tpl:apply mode="input/clear"/>
      </div>
      <!--
      <div class="filter sylma-hidder" js:class="sylma.crud.list.Filter">
        <tpl:apply mode="date">
          <tpl:read select="'{$alias}_to'" tpl:name="alias"/>
          <tpl:read select="parent()/dummy()/default()" tpl:name="value">
            <tpl:read select="'{$alias}_to'"/>
          </tpl:read>
        </tpl:apply>
       <tpl:apply mode="input/clear"/>
      </div>
      -->
    </div>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="input/value"/>

  <tpl:template match="sql:datetime" mode="input/events">
    <js:event name="change">
      %parent%.update();
    </js:event>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="filter/internal">
    <sql:filter optional="x" op="&gt;=">
      <tpl:read select="parent()/dummy()/default()" tpl:name="value">
        <tpl:read select="'{alias()}_from'"/>
      </tpl:read>
    </sql:filter>
    <!--
    <sql:filter optional="x" op="&lt;=">
      <le:get-argument optional="x" source="post">
        <le:name>
          <tpl:read select="'{alias()}_to'"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
    -->
  </tpl:template>

</crud:crud>
