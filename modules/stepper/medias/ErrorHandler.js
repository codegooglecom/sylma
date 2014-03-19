
sylma.stepper.ErrorHandler = new Class({

  errors : [],
  childErrors : 0,

  addError : function(type, message) {

    var error = {
      type : type,
      message : message
    };

    this.errors.push(error);
    this.addParentError(error);

    this.hasError(true);
  },

  clearErrors : function() {
//console.log(this);
    this.clearParentError(this.errors);

    this.errors = [];
    this.childErrors = 0;
  },

  addParentError : function(error) {

    var parent = this.getList();
    parent && parent.addChildError(error);
  },

  clearParentError : function(errors) {

    var parent = this.getList();
    parent && parent.clearChildError(errors);
  },

  getList : function() {

    return this.getParent();
  },

  addChildError : function(error) {

    this.childErrors++;
    this.hasError(true);

    this.addParentError(error);
  },

  clearChildError : function(errors) {

    this.childErrors -= errors.length;
    this.hasError(this.childErrors);

    this.clearParentError(errors);
  },

  hasError : function(value) {
    //if (this.getAlias && this.getAlias() === 'page' && value === false) throw 'bad';
//console.log(value, this.getAlias && this.getAlias());
    var node = this.getNode();

    if (value !== undefined) {

      node.toggleClass('error', value);

      if (!value) {

        this.clearErrors();
      }
    }

    return node.hasClass('error');
  }
});