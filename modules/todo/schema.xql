<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma,org/modules/todo"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:stat="http://2013.sylma,org/modules/todo/statut"
>

  <table name="todo">
    <field name="id" type="sql:id"/>
    <field name="description" type="sql:string-long"/>
    <field name="url" title="Page" type="sql:string"/>
    <field name="update" type="sql:datetime"/>
    <field name="insert" type="sql:datetime" default="now()" alter-default="null"/>
    <field name="priority" type="sql:int" default="0"/>
    <foreign name="statut" title="statut" occurs="0..1" table="stat:todo_statut" import="statut.xql" default="1"/>
  </table>

</schema>

