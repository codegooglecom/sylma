<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/storage/sql/test/city"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"

  xmlns:country="http://2013.sylma.org/storage/sql/test/country"
  xmlns:user="http://2013.sylma.org/view/test/sample1"
>

  <table name="city01" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <foreign occurs="0..1" name="country" table="country:country01" import="country01.xql"/>

    <reference name="users" table="user:user6b" foreign="user:city" import="user6b.xql"/>

  </table>

</schema>

