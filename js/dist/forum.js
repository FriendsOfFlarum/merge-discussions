module.exports =
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./forum.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./forum.js":
/*!******************!*\
  !*** ./forum.js ***!
  \******************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_forum__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/forum */ "./src/forum/index.js");
/* empty/unused harmony star reexport */

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _inheritsLoose; });
function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  subClass.__proto__ = superClass;
}

/***/ }),

/***/ "./src/forum/components/DiscussionMergeModal.js":
/*!******************************************************!*\
  !*** ./src/forum/components/DiscussionMergeModal.js ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DiscussionMergeModal; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Modal */ "flarum/components/Modal");
/* harmony import */ var flarum_components_Modal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Modal__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _DiscussionSearch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./DiscussionSearch */ "./src/forum/components/DiscussionSearch.js");





var DiscussionMergeModal =
/*#__PURE__*/
function (_Modal) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(DiscussionMergeModal, _Modal);

  function DiscussionMergeModal(discussion) {
    var _this;

    _this = _Modal.call(this) || this;
    _this.first = discussion;
    return _this;
  }

  var _proto = DiscussionMergeModal.prototype;

  _proto.init = function init() {
    _Modal.prototype.init.call(this);

    this.query = m.prop('');
    this.results = [];
  };

  _proto.title = function title() {
    return app.translator.trans('fof-merge-discussions.forum.modal.title');
  };

  _proto.content = function content() {
    return m("div", {
      className: "Modal-body"
    }, m("div", {
      className: "Form Form--centered"
    }, m("div", {
      className: "Form-group"
    }, _DiscussionSearch__WEBPACK_IMPORTED_MODULE_3__["default"].component({
      onSelect: this.select.bind(this),
      ignore: this.first.id()
    })), m("div", {
      className: "Form-group"
    }, m("p", null, "Merge ", m("b", null, this.first.title()), " into ", m("b", null, this.second && this.second.title() || '??')), flarum_components_Button__WEBPACK_IMPORTED_MODULE_1___default.a.component({
      className: 'Button Button--primary Button--block',
      type: 'submit',
      onclick: this.submit.bind(this),
      loading: this.loading,
      disabled: !this.first || !this.second,
      children: app.translator.trans('fof-merge-discussions.forum.modal.submit_button')
    }))));
  };

  _proto.select = function select(discussion) {
    if (discussion && discussion.id() === this.first.id()) return;
    this.second = discussion;
  };

  _proto.submit = function submit(e) {
    e.preventDefault();
    this.loading = true;
    return app.request({
      url: app.forum.attribute('apiUrl') + "/discussions/merge",
      method: 'POST',
      data: {
        ids: [this.first.id(), this.second.id()]
      },
      errorHandler: this.onerror.bind(this)
    }).then(function (res) {
      console.log(res);
    });
  };

  return DiscussionMergeModal;
}(flarum_components_Modal__WEBPACK_IMPORTED_MODULE_2___default.a);



/***/ }),

/***/ "./src/forum/components/DiscussionMergePost.js":
/*!*****************************************************!*\
  !*** ./src/forum/components/DiscussionMergePost.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DiscussionMergePost; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_EventPost__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/EventPost */ "flarum/components/EventPost");
/* harmony import */ var flarum_components_EventPost__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_EventPost__WEBPACK_IMPORTED_MODULE_1__);



var DiscussionMergePost =
/*#__PURE__*/
function (_EventPost) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(DiscussionMergePost, _EventPost);

  function DiscussionMergePost() {
    return _EventPost.apply(this, arguments) || this;
  }

  var _proto = DiscussionMergePost.prototype;

  /**
   * Get the name of the event icon.
   *
   * @return {String}
   */
  _proto.icon = function icon() {
    return 'fas fa-code-branch fa-flip-vertical';
  }
  /**
   * Get the translation key for the description of the event.
   *
   * @return {String}
   */
  ;

  _proto.descriptionKey = function descriptionKey() {
    return 'fof-merge-discussions.forum.post.merged';
  }
  /**
   * Get the translation data for the description of the event.
   *
   * @return {Object}
   */
  ;

  _proto.descriptionData = function descriptionData() {
    return this.props.post.content();
  };

  return DiscussionMergePost;
}(flarum_components_EventPost__WEBPACK_IMPORTED_MODULE_1___default.a);



/***/ }),

/***/ "./src/forum/components/DiscussionSearch.js":
/*!**************************************************!*\
  !*** ./src/forum/components/DiscussionSearch.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DiscussionSearch; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_Search__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/Search */ "flarum/components/Search");
/* harmony import */ var flarum_components_Search__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Search__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_utils_ItemList__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/utils/ItemList */ "flarum/utils/ItemList");
/* harmony import */ var flarum_utils_ItemList__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_ItemList__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./DiscussionSearchSource */ "./src/forum/components/DiscussionSearchSource.js");





var DiscussionSearch =
/*#__PURE__*/
function (_Search) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(DiscussionSearch, _Search);

  function DiscussionSearch() {
    return _Search.apply(this, arguments) || this;
  }

  var _proto = DiscussionSearch.prototype;

  _proto.view = function view() {
    this.hasFocus = true;

    var vdom = _Search.prototype.view.call(this);

    vdom.attrs.className = 'MergeDiscussions-Search open ' + vdom.attrs.className.replace(/(focused|open)/g, '');
    return vdom;
  };

  _proto.sourceItems = function sourceItems() {
    var items = new flarum_utils_ItemList__WEBPACK_IMPORTED_MODULE_2___default.a();
    items.add('discussions', new _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_3__["default"](this.props.onSelect, this.props.ignore));
    return items;
  };

  return DiscussionSearch;
}(flarum_components_Search__WEBPACK_IMPORTED_MODULE_1___default.a);



/***/ }),

/***/ "./src/forum/components/DiscussionSearchSource.js":
/*!********************************************************!*\
  !*** ./src/forum/components/DiscussionSearchSource.js ***!
  \********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DiscussionSearchSource; });
/* harmony import */ var flarum_helpers_highlight__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/helpers/highlight */ "flarum/helpers/highlight");
/* harmony import */ var flarum_helpers_highlight__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_helpers_highlight__WEBPACK_IMPORTED_MODULE_0__);


var DiscussionSearchSource =
/*#__PURE__*/
function () {
  function DiscussionSearchSource(onSelect, ignore) {
    this.results = {};
    this.onSelect = onSelect;
    this.ignore = ignore;
  }

  var _proto = DiscussionSearchSource.prototype;

  _proto.search = function search(query) {
    var _this = this;

    query = query.toLowerCase();
    this.results[query] = [];
    var params = {
      filter: {
        q: query
      },
      page: {
        limit: 3
      }
    };
    return app.store.find('discussions', params).then(function (results) {
      _this.results[query] = results.filter(function (d) {
        return d.id() !== _this.ignore;
      });
    });
  };

  _proto.view = function view(query) {
    var _this2 = this;

    query = query.toLowerCase();
    var results = this.results[query] || [];
    return [results.map(function (discussion) {
      return m("li", {
        className: "DiscussionSearchResult",
        "data-index": 'discussions' + discussion.id()
      }, m("a", {
        onclick: function onclick() {
          return _this2.onSelect(discussion);
        }
      }, m("div", {
        className: "DiscussionSearchResult-title"
      }, flarum_helpers_highlight__WEBPACK_IMPORTED_MODULE_0___default()(discussion.title(), query))));
    })];
  };

  return DiscussionSearchSource;
}();



/***/ }),

/***/ "./src/forum/index.js":
/*!****************************!*\
  !*** ./src/forum/index.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/extend */ "flarum/extend");
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_extend__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/utils/DiscussionControls */ "flarum/utils/DiscussionControls");
/* harmony import */ var flarum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/DiscussionMergeModal */ "./src/forum/components/DiscussionMergeModal.js");
/* harmony import */ var _components_DiscussionMergePost__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/DiscussionMergePost */ "./src/forum/components/DiscussionMergePost.js");





app.initializers.add('fof/merge-discussions', function () {
  // app.models.discussions.prototype.canMerge = Model.attribute('canMerge');
  app.postComponents.discussionMerged = _components_DiscussionMergePost__WEBPACK_IMPORTED_MODULE_4__["default"];
  Object(flarum_extend__WEBPACK_IMPORTED_MODULE_0__["extend"])(flarum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_1___default.a, 'moderationControls', function (items, discussion) {
    // if (!discussion.canMerge()) return;
    items.add('fof-merge', flarum_components_Button__WEBPACK_IMPORTED_MODULE_2___default.a.component({
      icon: 'fas fa-code-branch fa-flip-vertical',
      children: app.translator.trans('fof-merge-discussions.forum.discussion.merge'),
      onclick: function onclick() {
        return app.modal.show(new _components_DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_3__["default"](discussion));
      }
    }));
  });
});

/***/ }),

/***/ "flarum/components/Button":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['components/Button']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Button'];

/***/ }),

/***/ "flarum/components/EventPost":
/*!*************************************************************!*\
  !*** external "flarum.core.compat['components/EventPost']" ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/EventPost'];

/***/ }),

/***/ "flarum/components/Modal":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['components/Modal']" ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Modal'];

/***/ }),

/***/ "flarum/components/Search":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['components/Search']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Search'];

/***/ }),

/***/ "flarum/extend":
/*!***********************************************!*\
  !*** external "flarum.core.compat['extend']" ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['extend'];

/***/ }),

/***/ "flarum/helpers/highlight":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['helpers/highlight']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['helpers/highlight'];

/***/ }),

/***/ "flarum/utils/DiscussionControls":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['utils/DiscussionControls']" ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/DiscussionControls'];

/***/ }),

/***/ "flarum/utils/ItemList":
/*!*******************************************************!*\
  !*** external "flarum.core.compat['utils/ItemList']" ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/ItemList'];

/***/ })

/******/ });
//# sourceMappingURL=forum.js.map