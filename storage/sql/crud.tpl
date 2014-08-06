<?xml version="1.0" encoding="utf-8"?>
<crud:crud
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"

  extends="/#sylma/crud/full.crd"
>

  <crud:route>

    <view:view name="list">

      <tpl:import>pager/pager.tpl</tpl:import>

    </view:view>

  </crud:route>

  <crud:group name="form">

    <tpl:import>crud/form.tpl</tpl:import>

  </crud:group>

  <crud:group name="list">

    <tpl:import>crud/list.tpl</tpl:import>

  </crud:group>

</crud:crud>
