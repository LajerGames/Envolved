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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 37);
/******/ })
/************************************************************************/
/******/ ({

/***/ 37:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(38);


/***/ }),

/***/ 38:
/***/ (function(module, exports) {

$(document).ready(function () {

    // Short cuts
    $(document).bind('keypress', function (e) {

        // Press ctrl + shift + s to save to SQLite
        if (e.which === 19 && e.shiftKey && e.ctrlKey) {

            openExportToSQLiteModal();
        }
    });

    $('#export-to-sqlite-link').on('click', function () {
        openExportToSQLiteModal();
    });

    function openExportToSQLiteModal() {

        var saveAsInput = $('#export-to-sqlite input#save_as'),
            storyID = saveAsInput.data('story-id');
        $.post('/get-story-info', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID
            }
        }, function (data) {
            var decodedData = JSON.parse(data);

            // Create save as name
            saveAsInput.val(decodedData.title);
            $('#export-to-sqlite').modal();
        });
    }

    $('#export-to-sqlite button.btn-primary').on('click', function () {
        $.post('/stories/' + $(this).data('story-id') + '/export', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                action: 'save',
                name: $('#export-to-sqlite input#save_as').val()
            }
        });
    });

    // Region: Builder

    // Are we looking at archs or are we looking at story points
    page = $('#story_arch_id').val() !== undefined ? 'points' : 'archs';

    // Run this once before resize
    checkContainerSize(page);
    $(window).resize(function () {

        checkContainerSize(page);
    });
});

// We need the next few functions in the global scope.. sorry!

checkContainerSize = function checkContainerSize(page) {
    // Story arch container
    var container = page == 'points' ? $('div.panel-body') : $('#story-arch-container'),
        containerWidth = container.width();

    // change css to elements according to the VP size
    var removeMarginRightFromEveryNth = 0;
    if (containerWidth >= 640 && containerWidth < 980) {
        removeMarginRightFromEveryNth = 2;
    } else if (containerWidth >= 980 && containerWidth < 1320) {
        removeMarginRightFromEveryNth = 3;
    } else if (containerWidth >= 1320 && containerWidth < 1660) {
        removeMarginRightFromEveryNth = 4;
    } else if (containerWidth >= 1660 && containerWidth < 2000) {
        removeMarginRightFromEveryNth = 5;
    } else if (containerWidth >= 2000 && containerWidth < 2340) {
        removeMarginRightFromEveryNth = 6;
    } else if (containerWidth >= 2340 && containerWidth < 2680) {
        removeMarginRightFromEveryNth = 7;
    } else if (containerWidth >= 2680 && containerWidth < 3020) {
        removeMarginRightFromEveryNth = 8;
    } else if (containerWidth >= 3020 && containerWidth < 3360) {
        removeMarginRightFromEveryNth = 9;
    } else if (containerWidth >= 3360 && containerWidth < 3700) {
        removeMarginRightFromEveryNth = 10;
    } else {
        removeMarginRightFromEveryNth = 0;
    }

    removeRightMargin(removeMarginRightFromEveryNth, page);
};

removeRightMargin = function removeRightMargin(everyNthChild, page) {

    var margin = 40;

    if (everyNthChild < 2) {

        // Are we at story archs or story points
        if (page == 'points') {

            $('div.panel-body > .story-point-container').css('margin-right', margin + 'px');
            $('div.panel-body > .story-point-container').show(100);
        } else {

            $('#story-arch-container > .arch-container').css('margin-right', margin + 'px');
            $('#story-arch-container > .arch-container').show(100);
        }

        return;
    }

    // First set the setting on all of the elements
    if (page == 'points') {

        $('div.panel-body > .story-point-container').css('margin-right', margin + 'px');
        $('div.panel-body > .story-point-container:nth-child(' + everyNthChild + 'n)').css('margin-right', '0px');
        $('div.panel-body > .story-point-container').show(100);
    } else {

        $('#story-arch-container > .arch-container').css('margin-right', margin + 'px');
        $('#story-arch-container > .arch-container:nth-child(' + everyNthChild + 'n)').css('margin-right', '0px');
        $('#story-arch-container > .arch-container').show(100);
    }
};

/***/ })

/******/ });