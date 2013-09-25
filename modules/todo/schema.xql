<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma,org/modules/todo"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:stat="http://2013.sylma,org/modules/todo/statut"
  xmlns:proj="http://2013.sylma,org/modules/todo/project"
>

  <table name="todo" connection="common">
    <field name="id" type="sql:id"/>
    <field name="description" type="sql:string-long"/>
    <field name="url" title="Page" type="sql:string" default="null"/>
    <field name="update" type="sql:datetime"/>
    <field name="insert" type="sql:datetime" default="now()" alter-default="null"/>
    <field name="priority" type="sql:int" default="0"/>
    <foreign name="statut" occurs="0..1" table="stat:todo_statut" import="statut.xql" default="1"/>
    <foreign name="project" occurs="0..1" table="proj:todo_project" import="project.xql"/>
  </table>

</schema>

