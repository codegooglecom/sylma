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
>

  <tpl:template match="*" mode="filters">

    <js:include>Filter.js</js:include>

    <tr class="filters sylma-hidder" js:node="filters">
      <th>
        <a>filters</a>
      </th>
      <tpl:apply use="list-cols" mode="filter"/>
    </tr>

  </tpl:template>

  <tpl:template match="*" mode="filter">
    <td js:class="sylma.crud.list.Filter">
      <tpl:apply mode="filter/content"/>
    </td>
  </tpl:template>

  <tpl:template match="*" mode="filter/content">
    <tpl:apply mode="input/empty"/>
    <tpl:apply mode="input/clear"/>
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

  <tpl:template match="sql:table" mode="filters/prepare">
    <tpl:apply use="list-cols" mode="filter/internal"/>
  </tpl:template>

  <tpl:template match="*" mode="filter/internal">
    <sql:filter optional="x">
      <le:get-argument optional="x" source="post">
        <le:name>
          <tpl:read select="alias()"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
  </tpl:template>

  <tpl:template match="sql:string" mode="filter/internal">
    <sql:filter optional="x" op="search">
      <le:get-argument optional="x" source="post">
        <le:name>
          <tpl:read select="alias()"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
  </tpl:template>

  <!-- sql:datetime -->

  <tpl:template match="sql:datetime" mode="filter">
    <td>
      <tpl:apply mode="filter/date">
        <tpl:text tpl:name="alias">from</tpl:text>
      </tpl:apply>
      <div class="sylma-hidder" style="height: 0" js:node="to">
        <tpl:apply mode="filter/date">
          <tpl:text tpl:name="alias">to</tpl:text>
        </tpl:apply>
      </div>
    </td>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="filter/date">
    <tpl:argument name="alias" default="{alias('form')}{$alias}"/>
    <div js:class="sylma.crud.list.Filter">
      <tpl:apply mode="date">
        <tpl:read select="'{alias('form')}_{$alias}'" tpl:name="alias"/>
      </tpl:apply>
     <tpl:apply mode="input/clear"/>
    </div>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="input/events">
    <js:event name="change">
      %parent%.update();
    </js:event>
  </tpl:template>

  <tpl:template match="sql:datetime" mode="filter/internal">
    <sql:filter optional="x" op="&gt;=">
      <le:get-argument optional="x" source="post">
        <le:name>
          <tpl:read select="'{alias()}_from'"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
    <sql:filter optional="x" op="&lt;=">
      <le:get-argument optional="x" source="post">
        <le:name>
          <tpl:read select="'{alias()}_to'"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
  </tpl:template>

</tpl:collection>
