<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/view/test/sample1"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"

  xmlns:group="http://2013.sylma.org/view/test/sample2"
  xmlns:tag="http://2013.sylma.org/view/test/tag"
>

  <table name="fm_user" connection="test">

    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>

    <foreign occurs="0..n" name="tags" table="tag:fm_tag" junction="fm_user_tag" import="foreign-multiple-tag.xql"/>
    <foreign occurs="1..1" name="group" table="group:fm_group" import="foreign-multiple-group.xql"/>

  </table>

</schema>

