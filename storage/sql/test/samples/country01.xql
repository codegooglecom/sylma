<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/storage/sql/test/country"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"

  xmlns:city="http://2013.sylma.org/storage/sql/test/city"
>

  <table name="country01" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>

    <reference name="cities" table="city:city01" foreign="city:country" import="city01.xql"/>
    
  </table>

</schema>

