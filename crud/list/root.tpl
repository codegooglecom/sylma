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

  <tpl:import>filters.tpl</tpl:import>
  <tpl:import>../form.tpl</tpl:import>

  <tpl:template mode="list/js">
    <js:include>../Form.js</js:include>
    <js:include>Table.js</js:include>
    <js:include>Head.js</js:include>
    <js:include>Row.js</js:include>
  </tpl:template>

  <tpl:template mode="list/css">
    <le:context name="css">
      <le:file>list.less</le:file>
    </le:context>
  </tpl:template>

  <tpl:template>

    <tpl:apply mode="list/js"/>
    <tpl:apply mode="list/css"/>

    <tpl:apply mode="title">
      <tpl:read tpl:name="title" select="static()/title()"/>
    </tpl:apply>

    <div>
      <form class="list" js:class="sylma.crud.list.Table" action="" method="post" js:parent-name="table">

        <tpl:token name="action">
          <crud:path/>/default/list.json
        </tpl:token>

        <tpl:apply mode="actions"/>
        <tpl:apply mode="order"/>

        <tpl:apply select="static()" mode="filters"/>

        <table js:node="table" class="sql-{static()/name()}">
          <tpl:apply select="static()" mode="head/row"/>
          <crud:include path="list"/>
        </table>
      </form>
    </div>

  </tpl:template>

  <tpl:template mode="order/prepare">

    <le:argument name="sylma-order" format="string" source="post">
      <le:default>
        <tpl:apply select="$$list-order"/>
      </le:default>
    </le:argument>

  </tpl:template>

  <tpl:template mode="order">

    <tpl:apply mode="order/prepare"/>

    <tpl:variable name="order">
      <le:get-argument name="sylma-order" source="post"/>
    </tpl:variable>
    <input type="hidden" name="sylma-order" value="{$order}" js:node="order"/>

  </tpl:template>

  <tpl:template mode="actions">

    <div class="actions" js:class="sylma.ui.Base">
      <a class="button">
        <tpl:token name="href">
          <le:path/>/insert
        </tpl:token>
        Insert
      </a>
      <!--
      <a class="button" href="javascript:void(0)">
        <js:event name="click">
          %parent%.getObject('filters').toggleShow();
        </js:event>
        <tpl:text>Filters</tpl:text>
      </a>
      -->
      <a class="button" ls:owner="root" ls:group="admin" ls:mode="700">
        <tpl:token name="href">
          <le:path>/sylma/storage/sql/alter</le:path>?path=<view:get-schema/>
        </tpl:token>
        Structure
      </a>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="head/row">
    <thead>
      <tr js:class="sylma.ui.Base" js:name="head">
        <th>
          <tpl:apply mode="head/corner"/>
        </th>
        <tpl:apply use="list-cols" mode="head/cell"/>
      </tr>
    </thead>
  </tpl:template>

  <tpl:template match="*" mode="head/corner">
    <a href="javascript:void(0)" class="button">
      <tpl:text>F</tpl:text>
      <js:event name="click">
        %object%.getParent('table').getObject('filters').toggleShow();
      </js:event>
    </a>
  </tpl:template>

  <tpl:template match="*" mode="head/cell">
    <th>
      <a href="#" class="order" js:class="sylma.crud.list.Head">
        <js:option name="name"><tpl:apply select="alias()"/></js:option>
        <js:event name="click">
          %object%.update();
          e.preventDefault();
        </js:event>
        <tpl:apply select="title()"/>
      </a>
    </th>
  </tpl:template>

  <!-- Internal list -->

  <tpl:template mode="internal">

    <tpl:apply mode="init"/>

    <tbody js:name="container" js:class="sylma.ui.Container">

      <tpl:apply mode="init-container"/>

      <tpl:if test="has-children()">

        <tpl:apply select="*" mode="row"/>

        <tpl:else>
          <tr>
            <td colspan="99">
              <p class="sylma-noresult">No result</p>
            </td>
          </tr>
        </tpl:else>
      </tpl:if>

      <tr>
        <td colspan="99">
          <tpl:apply select="pager()"/>
        </td>
      </tr>

    </tbody>

  </tpl:template>

  <tpl:template mode="init">


<!--
    <tpl:apply select="static()" mode="filters/prepare"/>
-->
    <tpl:apply mode="init-pager"/>
    <tpl:apply mode="order/prepare"/>

    <sql:order>
      <le:get-argument name="sylma-order" source="post"/>
    </sql:order>

  </tpl:template>

  <tpl:template match="*" mode="row/init">
    <js:option name="url" cast="x">
      <le:path/>/update?id=<tpl:read select="id"/>
    </js:option>
  </tpl:template>

  <tpl:template match="*" mode="row">

    <tr js:class="sylma.crud.list.Row">
      <tpl:apply mode="row/init"/>
      <js:event name="click">
        %object%.onClick(e);
      </js:event>
      <tpl:apply mode="init-row"/>
      <td>
        <tpl:apply mode="row/action"/>
      </td>
      <tpl:apply use="list-cols" mode="cell"/>
    </tr>
  </tpl:template>

  <tpl:template match="*" mode="row/action">
    <a title="Editer" class="button">
      <tpl:token name="href">
        <le:path/>/update?id=<tpl:read select="id"/>
      </tpl:token>
      E
    </a>
  </tpl:template>

  <tpl:template match="*" mode="cell">
    <tpl:apply mode="filter/internal"/>
    <td>
      <tpl:apply mode="cell/content"/>
    </td>
  </tpl:template>

  <tpl:template match="*" mode="cell/content">
    <tpl:apply/>
  </tpl:template>

</tpl:collection>
