/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!***********************************!*\
  !*** ./src/admin/admin-topics.ts ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
Object(function webpackMissingModule() { var e = new Error("Cannot find module 'vue'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());

(function () {
    var elem = ".admin-topics";
    // @ts-ignore
    var exist1 = jQuery(elem).length;
    console.log({ exist1: exist1 }, elem);
    function loadInstance1() {
        var app = Object(function webpackMissingModule() { var e = new Error("Cannot find module 'vue'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())({
            created: function () {
                jQuery(elem).css("display", "block");
                jQuery(".mpereere-vue-loading").css("display", "none");
            }
        });
        app.mount(elem);
    }
    if (exist1) {
        loadInstance1();
    }
    else {
        console.log({ exist1: exist1 });
    }
})();

/******/ })()
;
//# sourceMappingURL=admin-topics.js.map