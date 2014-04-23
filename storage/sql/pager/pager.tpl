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

  <view:template mode="pager/init">

    <js:include>Pager.js</js:include>

  </view:template>

  <view:template mode="pager/argument">

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

  </view:template>

  <view:template match="*" mode="pager/dummy">

    <tpl:apply mode="pager/init"/>

    <sql:pager>
      <sql:current>
        <tpl:read select="dummy()/sylma-page"/>
      </sql:current>
      <sql:count>
        <tpl:apply mode="pager/count"/>
      </sql:count>
    </sql:pager>

  </view:template>

  <tpl:template mode="pager/count">10</tpl:template>

  <view:template match="sql:pager">

    <tpl:apply select="init()"/>

    <div class="pager" js:class="sylma.crud.Pager" js:name="pager">
<!--
      <tpl:if test="!is-multiple()">
        <tpl:token name="class">form-disable</tpl:token>
      </tpl:if>
-->
      <div class="clearfix">

        <a href="#" title="Page précédente" class="button pager-previous">
          <tpl:if test="is-first()">
            <tpl:token name="class">form-disable</tpl:token>
          </tpl:if>
          <js:option name="prev">
            <tpl:apply select="previous"/>
          </js:option>
          <js:event name="click">
            %object%.goPage(%object%.get('prev'), e);
          </js:event>
          &lt;&lt;
        </a>

        <span class="button pager-infos">

          <a href="#" title="Première page" class="pager-current">
            <tpl:if test="is-first()">
              <tpl:token name="class">form-disable</tpl:token>
            </tpl:if>
            <js:event name="click">
              %object%.goPage(1, e);
            </js:event>
            <tpl:read select="current"/>
          </a>

          <span class="pager-separator">/</span>

          <a href="#" title="Dernière page" class="pager-total">
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

        <a href="#" title="Page suivante" class="button pager-next">
          <tpl:if test="is-last()">
            <tpl:token name="class">form-disable</tpl:token>
          </tpl:if>
          <js:option name="next">
            <tpl:apply select="next"/>
          </js:option>
          <js:event name="click">
            %object%.goPage(%object%.get('next'), e);
          </js:event>
          &gt;&gt;
        </a>

        <input type="hidden" name="sylma-page" value="{current}" js:node="input"/>

      </div>
    </div>

  </view:template>

</view:view>
