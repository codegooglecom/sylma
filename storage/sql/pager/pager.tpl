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

  <tpl:template mode="pager/init">

    <js:include>Pager.js</js:include>

  </tpl:template>

  <!-- @deprecated, use pager/post, dummy or get instead -->
  <tpl:template mode="pager/argument">
    <tpl:deprecated/>
  </tpl:template>

  <tpl:template mode="pager/post">

    <tpl:apply mode="pager/init"/>

    <le:argument name="sylma-page" format="integer" source="post">
      <le:default>1</le:default>
    </le:argument>

    <sql:pager>
      <sql:current>
        <le:get-argument name="sylma-page" source="post"/>
      </sql:current>
      <sql:count>
        <tpl:apply mode="pager/count"/>
      </sql:count>
    </sql:pager>

  </tpl:template>

  <tpl:template mode="pager/get">

    <tpl:apply mode="pager/init"/>

    <le:argument name="sylma-page" format="integer">
      <le:default>1</le:default>
    </le:argument>

    <sql:pager>
      <sql:current>
        <le:get-argument name="sylma-page"/>
      </sql:current>
      <sql:count>
        <tpl:apply mode="pager/count"/>
      </sql:count>
    </sql:pager>

  </tpl:template>

  <tpl:template match="*" mode="pager/dummy">

    <tpl:apply mode="pager/init"/>

    <sql:pager>
      <sql:current>
        <tpl:read select="dummy()/sylma-page"/>
      </sql:current>
      <sql:count>
        <tpl:apply mode="pager/count"/>
      </sql:count>
    </sql:pager>

  </tpl:template>

  <tpl:template mode="pager/count">10</tpl:template>

  <tpl:template match="sql:pager">

    <tpl:apply select="init()"/>

    <div class="pager" js:class="sylma.crud.Pager" js:name="pager">

      <div class="clearfix">

        <tpl:apply mode="pager/previous"/>
        <tpl:apply mode="pager/current"/>
        <tpl:apply mode="pager/next"/>

        <input type="hidden" name="sylma-page" value="{current}" js:node="input"/>

      </div>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="pager/previous">

    <a href="javascript:void(0)" title="Page précédente" class="button pager-previous previous">
      <tpl:if test="is-first()">
        <tpl:token name="class">form-disable</tpl:token>
      </tpl:if>
      <js:option name="prev">
        <tpl:apply select="previous"/>
      </js:option>
      <js:event name="click">
        %object%.goPage(%object%.get('prev'), e);
      </js:event>
      <tpl:apply mode="pager/previous/content"/>
    </a>

  </tpl:template>

  <tpl:template match="*" mode="pager/previous/content">&lt;&lt;</tpl:template>

  <tpl:template match="*" mode="pager/current">

    <span class="button pager-infos">

      <a href="javascript:void(0)" title="Première page" class="pager-current">
        <tpl:if test="is-first()">
          <tpl:token name="class">form-disable</tpl:token>
        </tpl:if>
        <js:event name="click">
          %object%.goPage(1, e);
        </js:event>
        <tpl:read select="current"/>
      </a>

      <span class="pager-separator">/</span>

      <a href="javascript:void(0)" title="Dernière page" class="pager-total">
        <tpl:if test="is-last()">
          <tpl:token name="class">form-disable</tpl:token>
        </tpl:if>
        <js:option name="last">
          <tpl:apply select="last"/>
        </js:option>
        <js:event name="click">
          %object%.goPage(%object%.get('last'), e);
        </js:event>
        <tpl:read select="last"/>
      </a>

    </span>

  </tpl:template>

  <tpl:template match="*" mode="pager/pages">
    <div class="pages">
      <tpl:apply select="pages()" mode="pager/page"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="pager/page">
    <a href="javascript:void(0)" class="page {(page = current ? 'active' : '')}" data-page="{page}">
      <js:event name="click">
        %object%.goPage(this.get('data-page'), e);
      </js:event>
      <tpl:read select="page"/>
    </a>
    <tpl:if test="page != last">-</tpl:if>
  </tpl:template>

  <tpl:template match="*" mode="pager/next">

    <a href="javascript:void(0)" title="Page suivante" class="button pager-next next">
      <tpl:if test="is-last()">
        <tpl:token name="class">form-disable</tpl:token>
      </tpl:if>
      <js:option name="next">
        <tpl:apply select="next"/>
      </js:option>
      <js:event name="click">
        %object%.goPage(%object%.get('next'), e);
      </js:event>
      <tpl:apply mode="pager/next/content"/>
    </a>

  </tpl:template>

  <tpl:template match="*" mode="pager/next/content">&gt;&gt;</tpl:template>

</view:view>
