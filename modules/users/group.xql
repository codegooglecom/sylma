<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/core/user/group"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:user="http://2013.sylma.org/core/user"
>

  <table name="group">

    <field name="id" type="sql:id"/>
    <field name="name" title="name" type="sql:string"/>

    <reference name="users" table="user:user" foreign="user:groups" import="schema.xql"/>

  </table>

</schema>

