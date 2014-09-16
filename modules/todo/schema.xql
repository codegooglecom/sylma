<?xml version="1.0" encoding="utf-8"?>
<schema
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:stat="http://2013.sylma.org/modules/todo/statut"
  xmlns:proj="http://2013.sylma.org/modules/todo/project"
  xmlns:tag="http://2013.sylma.org/modules/todo/tag"

  xmlns:user="http://2013.sylma.org/core/user"
  xmlns:group="http://2013.sylma.org/core/user/group"

  targetNamespace="http://2013.sylma.org/modules/todo"
>

  <table name="todo" connection="common">

    <field name="id" type="sql:id"/>

    <field name="description" type="sql:string-long"/>
    <field name="url" title="Page" type="sql:string" default="null"/>
    <field name="priority" type="sql:int" default="0"/>

    <field name="duration" type="sql:float" default="null"/>
    <field name="duration_sub" type="sql:float" default="null"/>
    <field name="duration_real" title="duration (real)" type="sql:float" default="null"/>
    <field name="duration_sub_real" type="sql:float" default="null"/>
    <field name="term" type="sql:datetime" default="null"/>
    <field name="term_real" title="term (real)" type="sql:datetime" default="null"/>

    <field name="update" type="sql:datetime"/>
    <field name="insert" type="sql:datetime" default="now()" alter-default="null"/>

    <foreign name="statut" occurs="0..1" table="stat:todo_statut" import="statut.xql" default="1"/>
    <foreign name="project" occurs="0..1" table="proj:todo_project" import="project.xql"/>

    <field name="owner" type="sql:string-short" default="null"/>
    <field name="delegate" type="sql:string-short" default="null"/>
    <!--
    <foreign name="owner" occurs="1..1" table="user:user" import="/#sylma/modules/users/schema.xql"/>
    <foreign name="delegate" occurs="1..1" table="group:group" import="/#sylma/modules/users/group.xql"/>
    -->
    <foreign name="parent" occurs="0..1" table="todo:todo" import="schema.xql"/>

    <reference name="tags" table="tag:todo_tag" foreign="tag:todo" import="tag.xql"/>

  </table>

</schema>

