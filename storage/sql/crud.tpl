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

      <crud:import>pager.tpl</crud:import>

    </view:view>

  </crud:route>

  <crud:route name="update" groups="form,crud">

    <view:view mode="view" groups="view">

      <tpl:import>crud/update.tpl</tpl:import>

    </view:view>

  </crud:route>

  <crud:group name="form">

    <crud:import>crud/form.tpl</crud:import>

  </crud:group>

  <crud:group name="list">

    <crud:import>crud/list.tpl</crud:import>

  </crud:group>

</crud:crud>
