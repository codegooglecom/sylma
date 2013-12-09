<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:xl="http://2013.sylma.org/storage/xml"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:build="http://2013.sylma.org/parser/reflector/builder"

  xmlns:cls="http://2013.sylma.org/core/factory"
  xmlns:test="http://2013.sylma.org/modules/stepper"
>

  <tpl:template>

    <div id="tester" js:class="sylma.stepper.Main" js:parent-name="main">
      <js:option name="directory" cast="x">
        <tpl:apply select="getDirectory()"/>
      </js:option>
      <js:option name="query">
        <le:path>query</le:path>
      </js:option>
      <js:option name="save">
        <le:path>save</le:path>
      </js:option>
      <js:option name="load">
        <le:path>load</le:path>
      </js:option>
      <js:option name="tests">
        <tpl:apply select="getTests()"/>
      </js:option>
      <div js:node="board" class="board">
        <div class="actions">
          <button class="record">
            <js:event name="click">%object%.record();</js:event>
            <span>●</span>
          </button>
          <button>
            <js:event name="click">%object%.test(0);</js:event>
            <tpl:text>▶</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.test();</js:event>
            <span>▹</span>
          </button>
          <button>
            <js:event name="click">%object%.getFrame().toggleClass('sylma-visible');</js:event>
            <tpl:text>◑</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.save();</js:event>
            <tpl:text>⇥</tpl:text>
          </button>
          <br/>
          <button>
            <js:event name="click">%object%.createTest();</js:event>
            <tpl:text>✚</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addWatcher();</js:event>
            <tpl:text>⌚</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addSnapshot();</js:event>
            <tpl:text>⚀</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addCall();</js:event>
            <tpl:text>✍</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addQuery();</js:event>
            <tpl:text>♦</tpl:text>
          </button>
        </div>
        <tpl:apply mode="test"/>
      </div>
      <iframe js:node="frame" class="sylma-hidder"/>
    </div>

  </tpl:template>

  <tpl:template mode="test">
    <div js:class="sylma.stepper.Test" js:alias="test" class="test" js:parent-name="test">
      <tpl:apply mode="rename"/>
      <button class="edit">
        <js:event name="click">%object%.testFrom();</js:event>
        <span>▷</span>
      </button>
      <button class="edit">
        <js:event name="click">%object%.toggleSelect();</js:event>
        <span>▶</span>
      </button>
      <h4 js:node="name">
        <tpl:read select="file"/>
      </h4>
      <div js:node="pages" class="sylma-hidder zoom pages">
        <tpl:apply select="*"/>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="test:page">
    <ul js:class="sylma.stepper.Page" js:alias="page" class="page" js:parent-name="page">
      <tpl:apply mode="delete"/>
      <button class="edit">
        <js:event name="click">%object%.select(null, true);</js:event>
        <span>▶</span>
      </button>
      <tpl:apply mode="rename"/>
      <a href="{@url}" js:node="name">
        <js:event name="click">
          e.preventDefault();
          %object%.go();
        </js:event>
        <tpl:read select="@url"/>
      </a>
      <tpl:apply select="steps"/>
    </ul>
  </tpl:template>

  <tpl:template match="test:steps">
    <div js:class="sylma.ui.Template" js:alias="steps" js:all="x" js:autoload="x">
      <tpl:apply select="*"/>
    </div>
  </tpl:template>

  <tpl:template match="test:*" mode="title">
    <strong><tpl:read select="name()"/></strong>
  </tpl:template>

  <tpl:template match="test:*" mode="selector">
    <div js:class="sylma.stepper.Selector" js:alias="selector" class="selector clearfix" title="{@element}">
      <button js:node="toggle" class="sylma-hidder edit">
        <js:event name="click">%object%.activate(function(target) { this.changeElement(target); }.bind(%object%));</js:event>
        <tpl:text>◌</tpl:text>
      </button>
      <button class="sylma-hidder edit">
        <js:event name="click">%object%.selectNext();</js:event>
        <tpl:text>▹</tpl:text>
      </button>
      <button class="sylma-hidder edit">
        <js:event name="click">%object%.selectPrevious();</js:event>
        <tpl:text>◃</tpl:text>
      </button>
      <button class="sylma-hidder edit">
        <js:event name="click">%object%.selectChild();</js:event>
        <tpl:text>▿</tpl:text>
      </button>
      <button class="sylma-hidder edit">
        <js:event name="click">%object%.selectParent();</js:event>
        <tpl:text>▵</tpl:text>
      </button>
      <a title="{@element}" js:node="display">
        <tpl:read select="@element"/>
      </a>
    </div>
  </tpl:template>

  <tpl:template match="test:*" mode="actions">
    <tpl:apply mode="delete"/>
    <tpl:apply mode="edit"/>
  </tpl:template>

  <tpl:template match="test:*" mode="delete">
    <button type="button" js:class="sylma.ui.Template" js:alias="delete" js:autoload="x" class="delete">
      <js:event name="click">%parent%.remove();</js:event>
      <tpl:text>✕</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="test:*" mode="edit">
    <button js:class="sylma.ui.Template" js:alias="edit" js:autoload="x" class="edit">
      <js:event name="click">this.blur(); %parent%.go();</js:event>
      <span>▶</span>
    </button>
  </tpl:template>

  <tpl:template match="test:*" mode="rename">
    <button js:class="sylma.ui.Template" js:alias="rename" js:autoload="x" class="edit">
      <js:event name="click">%parent%.editName();</js:event>
      <tpl:text>...</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="test:event">
    <li js:class="sylma.stepper.Event" js:alias="event">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:text> </tpl:text>
      <span>
        <tpl:read select="@name"/>
      </span>
      <tpl:apply mode="selector"/>
    </li>
  </tpl:template>

  <tpl:template match="test:watcher">
    <li js:class="sylma.stepper.Watcher" js:alias="watcher">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <button type="button">
          <js:event name="click">%object%.addProperty();</js:event>
          <tpl:text>+</tpl:text>
        </button>
        <button type="button">
          <js:event name="click">%object%.addVariable();</js:event>
          <tpl:text>$</tpl:text>
        </button>
        <tpl:apply select="property"/>
        <tpl:apply select="variable"/>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:property">
    <div js:class="sylma.stepper.Property" js:alias="property">
      <select js:node="name">
        <js:event name="change">%object%.onChange()</js:event>
        <option>&lt;choose&gt;</option>
        <option>reload</option>
        <option>children</option>
        <option>opacity</option>
        <option>height</option>
        <option>width</option>
        <option>margin-top</option>
        <option>margin-right</option>
        <option>margin-bottom</option>
        <option>margin-left</option>
        <option>top</option>
        <option>right</option>
        <option>bottom</option>
        <option>left</option>
        <option>scroll</option>
      </select>
      <span js:node="value">
        <tpl:read/>
      </span>
      <tpl:apply mode="delete"/>
    </div>
  </tpl:template>

  <tpl:template match="test:snapshot">
    <li js:class="sylma.stepper.Snapshot" js:alias="snapshot">
      <tpl:apply mode="actions"/>
      <button class="edit refresh sylma-hidder">
        <js:event name="click">%object%.refresh();</js:event>
        <span>↻</span>
      </button>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
    </li>
  </tpl:template>

  <tpl:template match="test:call">
    <li js:class="sylma.stepper.Call" js:alias="call">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <input type="text" js:node="path" value="{@path}"/>
        <button type="button">
          <js:event name="click">%object%.addVariable();</js:event>
          <tpl:text>$</tpl:text>
        </button>
        <tpl:apply select="variable"/>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:variable">
    <div class="variable" js:class="sylma.stepper.Variable" js:alias="variable">
      <tpl:apply mode="delete"/>
      <strong>variable</strong>
      <input type="text" js:node="name" value="{@name}"/>
    </div>
  </tpl:template>

  <tpl:template match="test:input">
    <li js:class="sylma.stepper.Input" js:alias="input">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <textarea js:node="input" type="text">
          <js:event name="keyup">
            %object%.updateElement();
          </js:event>
          <tpl:read/>
        </textarea>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:query">
    <li js:class="sylma.stepper.Query" js:alias="query">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <input js:node="value" type="text" value="{read()}"/>
        <input js:node="creation" type="text" value="{@creation}"/>
      </form>
    </li>
  </tpl:template>

</view:view>
