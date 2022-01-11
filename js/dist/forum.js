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
/*! exports provided: components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_forum__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/forum */ "./src/forum/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "components", function() { return _src_forum__WEBPACK_IMPORTED_MODULE_0__["components"]; });



/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _asyncToGenerator; });
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

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
/* harmony import */ var _setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPrototypeOf.js */ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  Object(_setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__["default"])(subClass, superClass);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _setPrototypeOf; });
function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/regenerator/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/@babel/runtime/regenerator/index.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! regenerator-runtime */ "./node_modules/regenerator-runtime/runtime.js");

/***/ }),

/***/ "./node_modules/regenerator-runtime/runtime.js":
/*!*****************************************************!*\
  !*** ./node_modules/regenerator-runtime/runtime.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var runtime = function (exports) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined; // More compressible than void 0.

  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  function define(obj, key, value) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
    return obj[key];
  }

  try {
    // IE 8 has a broken Object.defineProperty that only works on DOM objects.
    define({}, "");
  } catch (err) {
    define = function define(obj, key, value) {
      return obj[key] = value;
    };
  }

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []); // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.

    generator._invoke = makeInvokeMethod(innerFn, self, context);
    return generator;
  }

  exports.wrap = wrap; // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.

  function tryCatch(fn, obj, arg) {
    try {
      return {
        type: "normal",
        arg: fn.call(obj, arg)
      };
    } catch (err) {
      return {
        type: "throw",
        arg: err
      };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed"; // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.

  var ContinueSentinel = {}; // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.

  function Generator() {}

  function GeneratorFunction() {}

  function GeneratorFunctionPrototype() {} // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.


  var IteratorPrototype = {};
  define(IteratorPrototype, iteratorSymbol, function () {
    return this;
  });
  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));

  if (NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = GeneratorFunctionPrototype;
  define(Gp, "constructor", GeneratorFunctionPrototype);
  define(GeneratorFunctionPrototype, "constructor", GeneratorFunction);
  GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"); // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.

  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function (method) {
      define(prototype, method, function (arg) {
        return this._invoke(method, arg);
      });
    });
  }

  exports.isGeneratorFunction = function (genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor ? ctor === GeneratorFunction || // For the native GeneratorFunction constructor, the best we can
    // do is to check its .name property.
    (ctor.displayName || ctor.name) === "GeneratorFunction" : false;
  };

  exports.mark = function (genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      define(genFun, toStringTagSymbol, "GeneratorFunction");
    }

    genFun.prototype = Object.create(Gp);
    return genFun;
  }; // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.


  exports.awrap = function (arg) {
    return {
      __await: arg
    };
  };

  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);

      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;

        if (value && typeof value === "object" && hasOwn.call(value, "__await")) {
          return PromiseImpl.resolve(value.__await).then(function (value) {
            invoke("next", value, resolve, reject);
          }, function (err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return PromiseImpl.resolve(value).then(function (unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration.
          result.value = unwrapped;
          resolve(result);
        }, function (error) {
          // If a rejected Promise was yielded, throw the rejection back
          // into the async generator function so it can be handled there.
          return invoke("throw", error, resolve, reject);
        });
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new PromiseImpl(function (resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise = // If enqueue has been called before, then we want to wait until
      // all previous Promises have been resolved before calling invoke,
      // so that results are always delivered in the correct order. If
      // enqueue has not been called before, then it is important to
      // call invoke immediately, without waiting on a callback to fire,
      // so that the async generator function has the opportunity to do
      // any necessary setup in a predictable way. This predictability
      // is why the Promise constructor synchronously invokes its
      // executor callback, and why async functions synchronously
      // execute code before the first await. Since we implement simple
      // async functions in terms of async generators, it is especially
      // important to get this right, even though it requires care.
      previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, // Avoid propagating failures to Promises returned by later
      // invocations of the iterator.
      callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
    } // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).


    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);
  define(AsyncIterator.prototype, asyncIteratorSymbol, function () {
    return this;
  });
  exports.AsyncIterator = AsyncIterator; // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.

  exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    if (PromiseImpl === void 0) PromiseImpl = Promise;
    var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl);
    return exports.isGeneratorFunction(outerFn) ? iter // If outerFn is a generator, return the full iterator.
    : iter.next().then(function (result) {
      return result.done ? result.value : iter.next();
    });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;
    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        } // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume


        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;

        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);

          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;
        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);
        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;
        var record = tryCatch(innerFn, self, context);

        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done ? GenStateCompleted : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };
        } else if (record.type === "throw") {
          state = GenStateCompleted; // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.

          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  } // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.


  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];

    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        // Note: ["return"] must be used for ES3 parsing compatibility.
        if (delegate.iterator["return"]) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError("The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (!info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value; // Resume execution at the desired location (see delegateYield).

      context.next = delegate.nextLoc; // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.

      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }
    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    } // The delegate iterator is finished, so forget it and continue with
    // the outer generator.


    context.delegate = null;
    return ContinueSentinel;
  } // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.


  defineIteratorMethods(Gp);
  define(Gp, toStringTagSymbol, "Generator"); // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.

  define(Gp, iteratorSymbol, function () {
    return this;
  });
  define(Gp, "toString", function () {
    return "[object Generator]";
  });

  function pushTryEntry(locs) {
    var entry = {
      tryLoc: locs[0]
    };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{
      tryLoc: "root"
    }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  exports.keys = function (object) {
    var keys = [];

    for (var key in object) {
      keys.push(key);
    }

    keys.reverse(); // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.

    return function next() {
      while (keys.length) {
        var key = keys.pop();

        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      } // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.


      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];

      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1,
            next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;
          return next;
        };

        return next.next = next;
      }
    } // Return an iterator with no values.


    return {
      next: doneResult
    };
  }

  exports.values = values;

  function doneResult() {
    return {
      value: undefined,
      done: true
    };
  }

  Context.prototype = {
    constructor: Context,
    reset: function reset(skipTempReset) {
      this.prev = 0;
      this.next = 0; // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.

      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;
      this.method = "next";
      this.arg = undefined;
      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" && hasOwn.call(this, name) && !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },
    stop: function stop() {
      this.done = true;
      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;

      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },
    dispatchException: function dispatchException(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;

      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !!caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }
          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }
          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }
          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },
    abrupt: function abrupt(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry && (type === "break" || type === "continue") && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },
    complete: function complete(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" || record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },
    finish: function finish(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },
    "catch": function _catch(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;

          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }

          return thrown;
        }
      } // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.


      throw new Error("illegal catch attempt");
    },
    delegateYield: function delegateYield(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  }; // Regardless of whether this script is executing as a CommonJS module
  // or not, return the runtime object so that we can declare the variable
  // regeneratorRuntime in the outer scope, which allows this module to be
  // injected easily by `bin/regenerator --include-runtime script.js`.

  return exports;
}( // If this script is executing as a CommonJS module, use module.exports
// as the regeneratorRuntime namespace. Otherwise create a new empty
// object. Either way, the resulting object will be used to initialize
// the regeneratorRuntime variable at the top of this file.
 true ? module.exports : undefined);

try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  // This module should not be running in strict mode, so the above
  // assignment should always work unless something is misconfigured. Just
  // in case runtime.js accidentally runs in strict mode, in modern engines
  // we can explicitly access globalThis. In older engines we can escape
  // strict mode using a global Function call. This could conceivably fail
  // if a Content Security Policy forbids using Function, but in that case
  // the proper solution is to fix the accidental strict mode problem. If
  // you've misconfigured your bundler to force strict mode and applied a
  // CSP to forbid Function, and you're not willing to fix either of those
  // problems, please detail your unique predicament in a GitHub issue.
  if (typeof globalThis === "object") {
    globalThis.regeneratorRuntime = runtime;
  } else {
    Function("r", "regeneratorRuntime = r")(runtime);
  }
}

/***/ }),

/***/ "./src/forum/addNotifications.js":
/*!***************************************!*\
  !*** ./src/forum/addNotifications.js ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_DiscussionMergedNotification__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/DiscussionMergedNotification */ "./src/forum/components/DiscussionMergedNotification.js");
/* harmony import */ var _extend_extendNotifications__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./extend/extendNotifications */ "./src/forum/extend/extendNotifications.ts");



/* harmony default export */ __webpack_exports__["default"] = (function () {
  flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.notificationComponents.discussionMerged = _components_DiscussionMergedNotification__WEBPACK_IMPORTED_MODULE_1__["default"];
  Object(_extend_extendNotifications__WEBPACK_IMPORTED_MODULE_2__["default"])();
});

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
/* harmony import */ var _babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_forum_components_DiscussionPage__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/forum/components/DiscussionPage */ "flarum/forum/components/DiscussionPage");
/* harmony import */ var flarum_forum_components_DiscussionPage__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_DiscussionPage__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_forum_components_IndexPage__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/forum/components/IndexPage */ "flarum/forum/components/IndexPage");
/* harmony import */ var flarum_forum_components_IndexPage__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_IndexPage__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! flarum/common/components/Modal */ "flarum/common/components/Modal");
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var flarum_forum_components_PostStream__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! flarum/forum/components/PostStream */ "flarum/forum/components/PostStream");
/* harmony import */ var flarum_forum_components_PostStream__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_PostStream__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var flarum_forum_states_PostStreamState__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! flarum/forum/states/PostStreamState */ "flarum/forum/states/PostStreamState");
/* harmony import */ var flarum_forum_states_PostStreamState__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_states_PostStreamState__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var flarum_forum_states_GlobalSearchState__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! flarum/forum/states/GlobalSearchState */ "flarum/forum/states/GlobalSearchState");
/* harmony import */ var flarum_forum_states_GlobalSearchState__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_states_GlobalSearchState__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! flarum/common/utils/Stream */ "flarum/common/utils/Stream");
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var flarum_common_utils_classList__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! flarum/common/utils/classList */ "flarum/common/utils/classList");
/* harmony import */ var flarum_common_utils_classList__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_classList__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _DiscussionSearch__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./DiscussionSearch */ "./src/forum/components/DiscussionSearch.js");















var DiscussionMergeModal = /*#__PURE__*/function (_Modal) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__["default"])(DiscussionMergeModal, _Modal);

  function DiscussionMergeModal() {
    return _Modal.apply(this, arguments) || this;
  }

  var _proto = DiscussionMergeModal.prototype;

  _proto.oninit = function oninit(vnode) {
    _Modal.prototype.oninit.call(this, vnode);

    this.discussion = this.attrs.discussion;
    this.type = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_11___default()('target');
    this.merging = [];

    if (this.attrs.preselect) {
      this.merging.push(this.attrs.preselect);
    }

    this.results = [];
    this.preview = null;
    this.loadingPreview = false;
    this.search = new flarum_forum_states_GlobalSearchState__WEBPACK_IMPORTED_MODULE_10___default.a();
  };

  _proto.title = function title() {
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.translator.trans('fof-merge-discussions.forum.modal.title');
  };

  _proto.className = function className() {
    return 'FoFMergeDiscussionsModal Modal--large';
  };

  _proto.content = function content() {
    var _this = this;

    return m("div", {
      className: "Modal-body"
    }, m("div", {
      className: "Form"
    }, m("div", {
      className: "Form-group"
    }, ['target', 'from'].map(function (key) {
      return m("div", null, m("input", {
        type: "radio",
        id: "type_" + key,
        checked: _this.type() === key,
        onclick: _this.changeType.bind(_this, key)
      }), "\xA0", m("label", {
        htmlFor: "type_" + key
      }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.translator.trans("fof-merge-discussions.forum.modal.type_" + key + "_label")));
    })), m("p", {
      className: "help"
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.translator.trans("fof-merge-discussions.forum.modal.type_" + this.type() + "_help_text", {
      title: this.discussion.title()
    })), m("div", {
      className: flarum_common_utils_classList__WEBPACK_IMPORTED_MODULE_12___default()('FormGroup', this.disabled() && 'hidden')
    }, m(_DiscussionSearch__WEBPACK_IMPORTED_MODULE_13__["default"], {
      state: this.search,
      onSelect: this.select.bind(this),
      ignore: this.discussion.id()
    })), m("div", {
      className: "Form-group MergeDiscussions-Discussions"
    }, m("ul", null, this.merging.map(function (d) {
      return m("li", null, m("i", {
        className: "fas fa-trash DeleteEntry-Button",
        onclick: function onclick() {
          return _this.remove(d);
        }
      }), "\xA0", m("a", {
        href: flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.forum.attribute('baseUrl') + "/d/" + d.id(),
        target: "_blank"
      }, m("i", null, d.id()), " ~ ", d.title()));
    }))), m("div", {
      className: "Form-group MergeDiscussions-Preview"
    }, m(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_6___default.a, {
      className: "Button Button--danger",
      onclick: this.loadPreview.bind(this),
      loading: this.loadingPreview,
      disabled: !this.discussion || !this.merging.length
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.translator.trans('fof-merge-discussions.forum.modal.load_preview_button')), this.preview && m("div", {
      className: "MergeDiscussions-PostStream"
    }, m("div", {
      className: "Hero"
    }, m("h2", null, this.type() === 'target' ? this.discussion.title() : this.merging[0].title())), m(flarum_forum_components_PostStream__WEBPACK_IMPORTED_MODULE_8___default.a, {
      stream: this.preview,
      discussion: this.preview.discussion,
      onPositionChange: function onPositionChange() {}
    }))), m("div", {
      className: "Form-group"
    }, m(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_6___default.a, {
      className: "Button Button--primary Button--block",
      type: "submit",
      onclick: this.submit.bind(this),
      loading: this.loading,
      disabled: !this.discussion || !this.merging.length
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.translator.trans('fof-merge-discussions.forum.modal.submit_button')))));
  };

  _proto.disabled = function disabled() {
    return this.type() === 'from' && this.merging.length !== 0;
  };

  _proto.select = function select(discussion) {
    if (discussion && discussion.id() === this.discussion.id()) return;

    if (!this.merging.includes(discussion) && !this.disabled()) {
      this.merging.push(discussion);
      delete this.preview;
    }
  };

  _proto.remove = function remove(discussion) {
    delete this.preview;
    this.merging.splice(this.merging.indexOf(this.merging.filter(function (d) {
      return d.id() === discussion.id();
    })[0]), 1);
  };

  _proto.changeType = function changeType(key) {
    this.type(key);
    if (this.merging.length > 1) this.merging = [];
  };

  _proto.loadPreview = function loadPreview() {
    var _this2 = this;

    this.loadingPreview = true;
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.request(this.getRequestData('GET')).then(function (payload) {
      var number = 1;
      if (payload.included) payload.included.map(flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store.pushObject.bind(flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store));
      payload.data.relationships.posts.data.map(function (record) {
        return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store.getById('posts', record.id);
      }).sort(function (a, b) {
        return a.createdAt() - b.createdAt();
      }).forEach(function (p, i) {
        p.number(number++);
        payload.data.relationships.posts.data[i] = {
          type: 'posts',
          id: p.id()
        };
      });
      var discussion = flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store.createRecord(payload.data.type, payload.data);
      discussion.payload = payload;
      _this2.loadingPreview = false;
      var includedPosts = discussion.posts();
      _this2.preview = new flarum_forum_states_PostStreamState__WEBPACK_IMPORTED_MODULE_9___default.a(discussion, includedPosts);
      m.redraw();
    })["catch"](function () {
      return _this2.loadingPreview = false;
    });
  };

  _proto.submit = function submit(e) {
    var _this3 = this;

    e.preventDefault();
    this.loading = true;
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.request(this.getRequestData()).then( /*#__PURE__*/Object(_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.mark(function _callee() {
      var _final;

      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              _final = _this3.type() === 'target' ? _this3.discussion : _this3.merging[0];

              if (!flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.current.matches(flarum_forum_components_DiscussionPage__WEBPACK_IMPORTED_MODULE_4___default.a)) {
                _context.next = 12;
                break;
              }

              if (!(_this3.type() === 'target')) {
                _context.next = 9;
                break;
              }

              _context.next = 5;
              return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store.find('discussions', _final.id());

            case 5:
              _context.next = 7;
              return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.current.get('stream').update();

            case 7:
              _context.next = 10;
              break;

            case 9:
              m.route.set(flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.route.discussion(_final));

            case 10:
              _context.next = 15;
              break;

            case 12:
              if (!flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.current.matches(flarum_forum_components_IndexPage__WEBPACK_IMPORTED_MODULE_5___default.a)) {
                _context.next = 15;
                break;
              }

              _context.next = 15;
              return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.store.find('discussions', _final.id());

            case 15:
              if (_this3.type() === 'target') {
                _this3.merging.forEach(function (d) {
                  return flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.discussions.removeDiscussion(d);
                });
              } else {
                flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.discussions.removeDiscussion(_this3.discussion);
              }

              flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.modal.close();

            case 17:
            case "end":
              return _context.stop();
          }
        }
      }, _callee);
    })))["catch"](console.error).then(this.loaded.bind(this));
  };

  _proto.getRequestData = function getRequestData(method) {
    if (method === void 0) {
      method = 'POST';
    }

    var isTarget = this.type() === 'target';
    var endpoint = isTarget ? this.discussion.apiEndpoint() : this.merging[0].apiEndpoint();
    var merging = isTarget ? this.merging.map(function (d) {
      return d.id();
    }) : this.discussion.id();
    return {
      method: method,
      url: "" + flarum_forum_app__WEBPACK_IMPORTED_MODULE_3___default.a.forum.attribute('apiUrl') + endpoint + "/merge",
      params: {
        ids: merging
      },
      body: {
        ids: merging
      },
      errorHandler: this.onerror.bind(this)
    };
  };

  return DiscussionMergeModal;
}(flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_7___default.a);



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
/* harmony import */ var flarum_forum_components_EventPost__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/components/EventPost */ "flarum/forum/components/EventPost");
/* harmony import */ var flarum_forum_components_EventPost__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_EventPost__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/helpers/punctuateSeries */ "flarum/common/helpers/punctuateSeries");
/* harmony import */ var flarum_common_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_2__);




var DiscussionMergePost = /*#__PURE__*/function (_EventPost) {
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
    var data = this.attrs.post.content();
    if (Array.isArray(data.titles)) data.titles = flarum_common_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_2___default()(data.titles).join('');
    return data;
  };

  return DiscussionMergePost;
}(flarum_forum_components_EventPost__WEBPACK_IMPORTED_MODULE_1___default.a);



/***/ }),

/***/ "./src/forum/components/DiscussionMergedNotification.js":
/*!**************************************************************!*\
  !*** ./src/forum/components/DiscussionMergedNotification.js ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DiscussionMergedNotification; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_forum_components_Notification__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/forum/components/Notification */ "flarum/forum/components/Notification");
/* harmony import */ var flarum_forum_components_Notification__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_Notification__WEBPACK_IMPORTED_MODULE_2__);




var DiscussionMergedNotification = /*#__PURE__*/function (_Notification) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(DiscussionMergedNotification, _Notification);

  function DiscussionMergedNotification() {
    return _Notification.apply(this, arguments) || this;
  }

  var _proto = DiscussionMergedNotification.prototype;

  _proto.icon = function icon() {
    return 'fas fa-code-branch fa-flip-vertical';
  };

  _proto.href = function href() {
    var notification = this.attrs.notification;
    var discussion = notification.subject();
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default.a.route.discussion(discussion);
  };

  _proto.content = function content() {
    var notification = this.attrs.notification;
    var user = notification.fromUser();
    var oldDiscussion = notification.content();
    var oldTitle = oldDiscussion.merged_title;
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default.a.translator.trans('fof-merge-discussions.forum.notification.discussion_merged', {
      user: user,
      oldTitle: oldTitle
    });
  };

  return DiscussionMergedNotification;
}(flarum_forum_components_Notification__WEBPACK_IMPORTED_MODULE_2___default.a);



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
/* harmony import */ var flarum_forum_components_Search__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/components/Search */ "flarum/forum/components/Search");
/* harmony import */ var flarum_forum_components_Search__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_Search__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/utils/ItemList */ "flarum/common/utils/ItemList");
/* harmony import */ var flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./DiscussionSearchSource */ "./src/forum/components/DiscussionSearchSource.js");





var DiscussionSearch = /*#__PURE__*/function (_Search) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(DiscussionSearch, _Search);

  function DiscussionSearch() {
    return _Search.apply(this, arguments) || this;
  }

  var _proto = DiscussionSearch.prototype;

  _proto.oncreate = function oncreate(vnode) {
    _Search.prototype.oncreate.call(this, vnode);

    console.log(this.attrs);
    this.navigator.onSelect(this.attrs.onSelect);
  };

  _proto.view = function view() {
    this.hasFocus = true;

    var vdom = _Search.prototype.view.call(this);

    vdom.attrs.className = "MergeDiscussions-Search " + (this.state.getValue() && 'open') + " " + vdom.attrs.className.replace(/(focused|open)/g, '');
    return vdom;
  };

  _proto.sourceItems = function sourceItems() {
    var items = new flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_2___default.a();
    items.add('discussions', new _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_3__["default"](this.attrs.onSelect, this.attrs.ignore));
    return items;
  };

  return DiscussionSearch;
}(flarum_forum_components_Search__WEBPACK_IMPORTED_MODULE_1___default.a);



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
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_helpers_highlight__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/helpers/highlight */ "flarum/common/helpers/highlight");
/* harmony import */ var flarum_common_helpers_highlight__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_highlight__WEBPACK_IMPORTED_MODULE_1__);



var DiscussionSearchSource = /*#__PURE__*/function () {
  function DiscussionSearchSource(onSelect, ignore) {
    this.results = new Map();
    this.onSelect = onSelect;
    this.ignore = ignore;
  }

  var _proto = DiscussionSearchSource.prototype;

  _proto.search = function search(query) {
    var _this = this;

    query = query.toLowerCase();
    var limit = flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.forum.attribute('fof-merge-discussions.search_limit') || 4;
    this.results.set(query, []);
    var params = {
      filter: {
        q: query
      },
      page: {
        limit: limit
      }
    };
    var id = Number(query);

    if (!Number.isNaN(id) && id !== this.ignore) {
      return flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.store.find('discussions', id).then(function (d) {
        _this.results.set(query, [d]);
      })["catch"](function () {
        return [];
      });
    }

    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.store.find('discussions', params).then(function (results) {
      _this.results.set(query, results.filter(function (d) {
        return d.id() !== _this.ignore;
      }));
    });
  };

  _proto.view = function view(query) {
    var _this2 = this;

    query = query.toLowerCase();
    var results = this.results.get(query) || [];
    return [results.map(function (discussion) {
      return m("li", {
        className: "DiscussionSearchResult",
        "data-index": 'discussions' + discussion.id()
      }, m("button", {
        className: "Button--ua-reset",
        type: "button",
        onclick: function onclick(e) {
          e.stopPropagation();

          _this2.onSelect(discussion);
        }
      }, m("div", {
        className: "DiscussionSearchResult-title"
      }, m("i", null, flarum_common_helpers_highlight__WEBPACK_IMPORTED_MODULE_1___default()(discussion.id(), query)), " ~ ", flarum_common_helpers_highlight__WEBPACK_IMPORTED_MODULE_1___default()(discussion.title(), query))));
    })];
  };

  return DiscussionSearchSource;
}();



/***/ }),

/***/ "./src/forum/components/index.js":
/*!***************************************!*\
  !*** ./src/forum/components/index.js ***!
  \***************************************/
/*! exports provided: components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "components", function() { return components; });
/* harmony import */ var _DiscussionMergedNotification__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DiscussionMergedNotification */ "./src/forum/components/DiscussionMergedNotification.js");
/* harmony import */ var _DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DiscussionMergeModal */ "./src/forum/components/DiscussionMergeModal.js");
/* harmony import */ var _DiscussionMergePost__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./DiscussionMergePost */ "./src/forum/components/DiscussionMergePost.js");
/* harmony import */ var _DiscussionSearch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./DiscussionSearch */ "./src/forum/components/DiscussionSearch.js");
/* harmony import */ var _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./DiscussionSearchSource */ "./src/forum/components/DiscussionSearchSource.js");





var components = {
  DiscussionMergedNotification: _DiscussionMergedNotification__WEBPACK_IMPORTED_MODULE_0__["default"],
  DiscussionMergeModal: _DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_1__["default"],
  DiscussionMergePost: _DiscussionMergePost__WEBPACK_IMPORTED_MODULE_2__["default"],
  DiscussionSearch: _DiscussionSearch__WEBPACK_IMPORTED_MODULE_3__["default"],
  DiscussionSearchSource: _DiscussionSearchSource__WEBPACK_IMPORTED_MODULE_4__["default"]
};

/***/ }),

/***/ "./src/forum/extend/extendNotifications.ts":
/*!*************************************************!*\
  !*** ./src/forum/extend/extendNotifications.ts ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_forum_components_NotificationGrid__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/forum/components/NotificationGrid */ "flarum/forum/components/NotificationGrid");
/* harmony import */ var flarum_forum_components_NotificationGrid__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_NotificationGrid__WEBPACK_IMPORTED_MODULE_2__);



/* harmony default export */ __webpack_exports__["default"] = (function () {
  Object(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__["extend"])(flarum_forum_components_NotificationGrid__WEBPACK_IMPORTED_MODULE_2___default.a.prototype, 'notificationTypes', function (items) {
    items.add('discussionMerged', {
      name: 'discussionMerged',
      icon: 'fas fa-code-branch fa-flip-vertical',
      label: flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.translator.trans('fof-merge-discussions.forum.notification.preferences.discussion_merged')
    });
  });
});

/***/ }),

/***/ "./src/forum/index.js":
/*!****************************!*\
  !*** ./src/forum/index.js ***!
  \****************************/
/*! exports provided: components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_Model__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/Model */ "flarum/common/Model");
/* harmony import */ var flarum_common_Model__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Model__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/forum/utils/DiscussionControls */ "flarum/forum/utils/DiscussionControls");
/* harmony import */ var flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _components_DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/DiscussionMergeModal */ "./src/forum/components/DiscussionMergeModal.js");
/* harmony import */ var _components_DiscussionMergePost__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/DiscussionMergePost */ "./src/forum/components/DiscussionMergePost.js");
/* harmony import */ var _addNotifications__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./addNotifications */ "./src/forum/addNotifications.js");
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components */ "./src/forum/components/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "components", function() { return _components__WEBPACK_IMPORTED_MODULE_8__["components"]; });










flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.initializers.add('fof/merge-discussions', function () {
  flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.store.models.discussions.prototype.canMerge = flarum_common_Model__WEBPACK_IMPORTED_MODULE_2___default.a.attribute('canMerge');
  flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.postComponents.discussionMerged = _components_DiscussionMergePost__WEBPACK_IMPORTED_MODULE_6__["default"];
  Object(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__["extend"])(flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4___default.a, 'moderationControls', function (items, discussion) {
    if (!discussion.canMerge()) return;
    items.add('fof-merge', flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      icon: 'fas fa-code-branch fa-flip-vertical',
      onclick: function onclick() {
        return flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.modal.show(_components_DiscussionMergeModal__WEBPACK_IMPORTED_MODULE_5__["default"], {
          discussion: discussion
        });
      }
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default.a.translator.trans('fof-merge-discussions.forum.discussion.merge')));
  });
  Object(_addNotifications__WEBPACK_IMPORTED_MODULE_7__["default"])();
});

/***/ }),

/***/ "flarum/common/Model":
/*!*****************************************************!*\
  !*** external "flarum.core.compat['common/Model']" ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/Model'];

/***/ }),

/***/ "flarum/common/components/Button":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Button']" ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/components/Button'];

/***/ }),

/***/ "flarum/common/components/Modal":
/*!****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Modal']" ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/components/Modal'];

/***/ }),

/***/ "flarum/common/extend":
/*!******************************************************!*\
  !*** external "flarum.core.compat['common/extend']" ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/extend'];

/***/ }),

/***/ "flarum/common/helpers/highlight":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/helpers/highlight']" ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/helpers/highlight'];

/***/ }),

/***/ "flarum/common/helpers/punctuateSeries":
/*!***********************************************************************!*\
  !*** external "flarum.core.compat['common/helpers/punctuateSeries']" ***!
  \***********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/helpers/punctuateSeries'];

/***/ }),

/***/ "flarum/common/utils/ItemList":
/*!**************************************************************!*\
  !*** external "flarum.core.compat['common/utils/ItemList']" ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/utils/ItemList'];

/***/ }),

/***/ "flarum/common/utils/Stream":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/utils/Stream']" ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/utils/Stream'];

/***/ }),

/***/ "flarum/common/utils/classList":
/*!***************************************************************!*\
  !*** external "flarum.core.compat['common/utils/classList']" ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['common/utils/classList'];

/***/ }),

/***/ "flarum/forum/app":
/*!**************************************************!*\
  !*** external "flarum.core.compat['forum/app']" ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/app'];

/***/ }),

/***/ "flarum/forum/components/DiscussionPage":
/*!************************************************************************!*\
  !*** external "flarum.core.compat['forum/components/DiscussionPage']" ***!
  \************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/DiscussionPage'];

/***/ }),

/***/ "flarum/forum/components/EventPost":
/*!*******************************************************************!*\
  !*** external "flarum.core.compat['forum/components/EventPost']" ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/EventPost'];

/***/ }),

/***/ "flarum/forum/components/IndexPage":
/*!*******************************************************************!*\
  !*** external "flarum.core.compat['forum/components/IndexPage']" ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/IndexPage'];

/***/ }),

/***/ "flarum/forum/components/Notification":
/*!**********************************************************************!*\
  !*** external "flarum.core.compat['forum/components/Notification']" ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/Notification'];

/***/ }),

/***/ "flarum/forum/components/NotificationGrid":
/*!**************************************************************************!*\
  !*** external "flarum.core.compat['forum/components/NotificationGrid']" ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/NotificationGrid'];

/***/ }),

/***/ "flarum/forum/components/PostStream":
/*!********************************************************************!*\
  !*** external "flarum.core.compat['forum/components/PostStream']" ***!
  \********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/PostStream'];

/***/ }),

/***/ "flarum/forum/components/Search":
/*!****************************************************************!*\
  !*** external "flarum.core.compat['forum/components/Search']" ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/components/Search'];

/***/ }),

/***/ "flarum/forum/states/GlobalSearchState":
/*!***********************************************************************!*\
  !*** external "flarum.core.compat['forum/states/GlobalSearchState']" ***!
  \***********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/states/GlobalSearchState'];

/***/ }),

/***/ "flarum/forum/states/PostStreamState":
/*!*********************************************************************!*\
  !*** external "flarum.core.compat['forum/states/PostStreamState']" ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/states/PostStreamState'];

/***/ }),

/***/ "flarum/forum/utils/DiscussionControls":
/*!***********************************************************************!*\
  !*** external "flarum.core.compat['forum/utils/DiscussionControls']" ***!
  \***********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['forum/utils/DiscussionControls'];

/***/ })

/******/ });
//# sourceMappingURL=forum.js.map