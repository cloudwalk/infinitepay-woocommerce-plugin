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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

(function ($) {
  "use strict";

  $(function () {
    var infinite_pay_submit = false;
    var additionalInfo = {};

    function getCheckoutForm() {
      return document.querySelector('#infinitepay-form');
    }

    function validateInputs() {
      clearErrors();
      var mainInputs = validateMainInputs();
      var additionalInputs = validateAdditionalInputs();

      if (mainInputs || additionalInputs) {
        focusInputWithError();
        return false;
      }

      return true;
    }

    function clearErrors() {
      for (var i = 0; i < document.querySelectorAll('[data-checkout]').length; i++) {
        var errorElement = document.querySelectorAll('[data-checkout]')[i];
        console.log(errorElement);
        errorElement.classList.remove('ip-form-control-error');
      }

      for (var j = 0; j < document.querySelectorAll('.ip-error').length; j++) {
        var errorMessage = document.querySelectorAll('.ip-error')[j];
        errorMessage.style.display = 'none';
      }
    }

    function validateMainInputs() {
      var hasEmpty = false;
      var form = getCheckoutForm();
      var formInputs = form.querySelectorAll('[data-checkout]');
      var mainInputs = ['installments', 'cardSecurityCode', 'cardExpirationDate', 'cardNumber', 'docNumber'];

      for (var k = 0; k < formInputs.length; k++) {
        var element = formInputs[k];

        if (mainInputs.indexOf(element.getAttribute('data-checkout')) > -1) {
          var withError = false;
          if (element.value === '-1' || element.value === '') withError = true;
          if (element.id === 'ip_docNumber') withError = !validateCpf(element.value);

          if (withError) {
            var errorSpan = form.querySelectorAll('span[data-main="#' + element.id + '"]');
            if (errorSpan.length > 0) errorSpan[0].style.display = 'inline-block';
            element.classList.add('ip-form-control-error');
            hasEmpty = true;
          }
        }
      }

      return hasEmpty;
    }

    function validateAdditionalInputs() {
      var hasEmpty = false;

      if (additionalInfo.cardholderIdentificationType) {
        var inputDocType = document.getElementById('docType');

        if (inputDocType.value === '-1' || inputDocType === '') {
          inputDocType.classList.add('ip-form-control-error');
          hasEmpty = true;
        }
      }

      if (additionalInfo.cardholderIdentificationNumber) {
        var inputDocNumber = document.getElementById('docNumber');

        if (inputDocNumber.value === '-1' || inputDocNumber === '') {
          inputDocNumber.classList.add('ip-form-control-error');
          document.getElementById('ip-error-1').style.display = 'inline-block';
          hasEmpty = true;
        }
      }

      return hasEmpty;
    }

    function validateCpf(cpf) {
      cpf = cpf.replace(/[^\d]+/g, '');
      if (cpf == '') return false;
      if (cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999") return false;
      var add = 0;

      for (var i = 0; i < 9; i++) {
        add += parseInt(cpf.charAt(i)) * (10 - i);
      }

      var rev = 11 - add % 11;
      if (rev == 10 || rev == 11) rev = 0;
      if (rev != parseInt(cpf.charAt(9))) return false;
      add = 0;

      for (var _i = 0; _i < 10; _i++) {
        add += parseInt(cpf.charAt(_i)) * (11 - _i);
      }

      rev = 11 - add % 11;
      if (rev == 10 || rev == 11) rev = 0;
      return rev == parseInt(cpf.charAt(10));
    }

    function focusInputWithError() {
      var inputsError = document.querySelectorAll('.ip-form-control-error');
      if (inputsError !== undefined) inputsError[0].focus();
    }

    function responseHandler(response) {
      var token = document.querySelector("#ip-token");
      token.value = response.token;
      infinite_pay_submit = false;
      var wooCheckoutForm = $('form.woocommerce-checkout');
      wooCheckoutForm.off('checkout_place_order', infinitePayFormHandler);
      wooCheckoutForm.submit();
    }

    function createToken() {
      clearErrors(); // TODO: show loading

      var form = getCheckoutForm();
      var number = document.querySelector('#ip_ccNo').value.replace(/\D+/g, '');
      var expire = document.querySelector('#ip_expdate').value;
      var cvv = document.querySelector('#ip_cvv').value;
      var token = "".concat(number, ":").concat(expire, ":").concat(cvv);
      responseHandler({
        token: token
      });
      return false;
    }

    function infinitePayFormHandler() {
      if (infinite_pay_submit) {
        infinite_pay_submit = false;
        return true;
      }

      if (!document.getElementById('payment_method_infinitepay').checked) return true;
      if (validateInputs()) return createToken();
      return false;
    }

    function init() {
      var uuid = wc_infinitepay_params.uuid;
      var script = document.createElement('script');
      script.setAttribute('src', 'https://authorizer-data.infinitepay.io/fp/tags.js?org_id=k8vif92e&session_id=cloudwalk' + uuid);
      script.setAttribute('type', 'text/javascript');
      document.head.appendChild(script);
      var iFrame = document.createElement('div');
      iFrame.setAttribute('id', 'iframe-cloudwatch-auth');
      iFrame.innerHTML = '<noscript><iframe style="width: 100px; height: 100px; border: 0; position:absolute; top: -5000px;" src="https://authorizer-data.infinitepay.io/fp/tags?org_id=k8vif92e&session_id=cloudwalk' + uuid + '"></iframe></noscript>';
      document.body.appendChild(iFrame);
      var inputUUID = document.querySelector("#ip-uuid");
      inputUUID.value = uuid;
    }

    $("form.woocommerce-checkout").on("checkout_place_order", infinitePayFormHandler);
    init();
  });
})(jQuery);

/***/ })

/******/ });
//# sourceMappingURL=index.js.map