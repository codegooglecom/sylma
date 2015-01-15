<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://2014.sylma.org/html"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:cls="http://2013.sylma.org/core/factory"
>

  <tpl:import>dummy.tpl</tpl:import>
  <tpl:import>filters.tpl</tpl:import>
  <tpl:import>../form.tpl</tpl:import>
  <tpl:import>resources.tpl</tpl:import>

  <tpl:template>

    <tpl:apply mode="list/js"/>
    <tpl:apply mode="list/css"/>

    <tpl:apply mode="title">
      <tpl:read tpl:name="title" select="static()/title()"/>
    </tpl:apply>

    <div>

      <tpl:apply mode="list/form"/>

    </div>

  </tpl:template>

  <tpl:template mode="list/form">

    <form class="list" js:class="sylma.crud.collection.Table" action="" method="post" js:parent-name="table">
      <tpl:apply mode="list/container"/>
    </form>

  </tpl:template>

  <tpl:template mode="list/container">

    <tpl:token name="action">
      <tpl:apply mode="list/path"/>
    </tpl:token>

    <tpl:apply mode="dummy"/>
    <tpl:apply mode="order"/>

    <tpl:apply select="init()"/>

    <tpl:apply mode="actions"/>

    <tpl:apply select="static()" mode="init"/>

    <table js:node="table" class="sql-{static()/name()}">
      <tpl:apply select="static()" mode="head/row"/>
      <tpl:apply mode="list/content"/>
    </table>

  </tpl:template>

  <tpl:template mode="list/path">
    <crud:path/>/default/list.json
  </tpl:template>

  <tpl:template mode="list/content">
    <crud:include path="list"/>
  </tpl:template>

  <tpl:template match="*" mode="init">

    <tpl:apply mode="filters"/>

  </tpl:template>

  <tpl:template match="*" mode="order">

    <tpl:apply mode="order/prepare"/>

    <tpl:variable name="value">
      <tpl:read select="dummy()/sylma-order"/>
    </tpl:variable>
    <input type="hidden" name="sylma-order" value="{$value}" js:node="order"/>

  </tpl:template>

  <tpl:template mode="actions">

    <div class="actions" js:class="sylma.ui.Base">
      <tpl:apply mode="actions/insert"/>
      <!--
      <a class="button" href="javascript:void(0)">
        <js:event name="click">
          %parent%.getObject('filters').toggleShow();
        </js:event>
        <tpl:text>Filters</tpl:text>
      </a>
      <a class="button" ls:owner="root" ls:group="admin" ls:mode="700">
        <tpl:token name="href">
          <le:path>/sylma/storage/sql/alter</le:path>?path=<view:get-schema/>
        </tpl:token>
        Structure
      </a>
      -->
    </div>

  </tpl:template>

  <tpl:template mode="actions/insert">
    <a class="button">
      <tpl:token name="href">
        <tpl:apply mode="actions/insert/href"/>
      </tpl:token>
      Insert
    </a>
  </tpl:template>

  <tpl:template match="*" mode="actions/insert/href">
    <le:path/>/insert
  </tpl:template>

  <tpl:template match="*" mode="head/row">
    <thead>
      <tr js:class="sylma.crud.collection.Sorter" js:name="head" js:parent-name="head">
        <tpl:apply mode="head/corner"/>
        <tpl:apply use="list-cols" mode="head/cell"/>
      </tr>
    </thead>
  </tpl:template>

  <tpl:template match="*" mode="head/corner">
    <th>
      <tpl:apply mode="head/corner/content"/>
    </th>
  </tpl:template>

  <tpl:template match="*" mode="head/corner/content">
    <a href="javascript:void(0)" class="button">
      <tpl:text>F</tpl:text>
      <js:event name="click">
        %object%.getParent('table').getObject('filters').toggleShow();
      </js:event>
    </a>
  </tpl:template>

  <tpl:template match="*" mode="head/cell">
    <tpl:variable name="alias">
      <tpl:apply mode="head/cell/alias"/>
    </tpl:variable>
    <th class="order-{$alias}">
      <a href="#" class="order" js:class="sylma.crud.collection.Head">
        <js:name>
          <tpl:read select="$alias"/>
        </js:name>
        <js:option name="name">
          <tpl:read select="$alias"/>
        </js:option>
        <js:event name="click">
          %object%.update();
          e.preventDefault();
        </js:event>
        <tpl:apply mode="head/cell/content"/>
      </a>
    </th>
  </tpl:template>

  <tpl:template match="*" mode="head/cell/alias">
    <tpl:apply select="alias()"/>
  </tpl:template>

  <tpl:template match="*" mode="head/cell/content">
    <tpl:apply select="title()"/>
  </tpl:template>

  <!-- Internal list -->

  <tpl:template mode="internal">

    <tpl:apply mode="dummy"/>

    <tpl:apply mode="pager/dummy"/>
    <tpl:apply mode="order/prepare"/>

    <tpl:apply select="init()"/>
    <tpl:apply select="counter()"/>

    <sql:order>
      <tpl:read select="dummy()/sylma-order"/>
    </sql:order>

    <tbody js:name="container" js:class="sylma.ui.Container">

      <tpl:apply mode="init-container"/>
      <tpl:apply select="static()" mode="init/internal"/>

      <tpl:apply select="dummy()/save()"/>

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

      <tpl:apply mode="pager"/>

    </tbody>

  </tpl:template>

  <tpl:template mode="pager">

    <tr>
      <td colspan="99">
        <tpl:apply select="pager()"/>
      </td>
    </tr>

  </tpl:template>

  <tpl:template  match="*" mode="pager/input/source">
    <tpl:read select="dummy()/sylma-page"/>
  </tpl:template>

  <tpl:template match="*" mode="init/internal">

    <tpl:apply use="list-cols" mode="filter/internal"/>

  </tpl:template>

  <tpl:template match="*" mode="row/init">
    <js:option name="url" cast="x">
      <tpl:apply mode="row/action/href"/>
    </js:option>
  </tpl:template>

  <tpl:template match="*" mode="row">

    <tr js:class="sylma.crud.collection.Row">
      <tpl:apply mode="row/init"/>
      <js:event name="click">
        %object%.onClick(e);
      </js:event>
      <td>
        <tpl:apply mode="row/action"/>
      </td>
      <tpl:apply use="list-cols" mode="cell"/>
    </tr>
  </tpl:template>

  <tpl:template match="*" mode="row/action">
    <a title="Editer" class="button">
      <tpl:token name="href">
        <tpl:apply mode="row/action/href"/>
      </tpl:token>
      E
    </a>
  </tpl:template>

  <tpl:template match="*" mode="row/action/href">
    <le:path/>/update?id=<tpl:read select="id"/>
  </tpl:template>

  <tpl:template match="*" mode="cell">
    <td class="cell-{alias()}">
      <tpl:apply mode="cell/content"/>
    </td>
  </tpl:template>

  <tpl:template match="*" mode="cell/content">
    <tpl:apply/>
  </tpl:template>

</tpl:collection>
