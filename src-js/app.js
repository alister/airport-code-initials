require('./css/responsive.css');
require('./css/style.css');
require('./css/app.css');

require('./typed.js');

require('./images/favicon.png');
require('./images/favicon-16x16.png');
require('./images/favicon-32x32.png');
require('./images/favicon-96x96.png');
require('./images/logo.png');

var airportCodes = require('./airportLookup');

airportCodeHandler = function() {
  const error = document.getElementById('airportCodeError');

  airportCodeResult.innerText = '';
  if (this.value.length > 3) {
    error.innerText = "too many characters - only enter 3 letters";
  } else if (this.value.length < 3) {
    error.innerText = "Not enough characters - enter 3 letters";
  } else {
    error.innerText = "";
    airportCodeResult.innerText = airportCodes(this.value);
  }
};

/*global $, jQuery, alert*/
$(document).ready(function() {
  'use strict';

  onload = function () {
    const e = document.getElementById('airportCode');
    e.oninput = airportCodeHandler;
    e.onpropertychange = e.oninput; // for IE8
    // e.onchange = e.oninput; // FF needs this in <select><option>...
    // other things for onload()
  };

  // ========================================================================= //
  //  //NAVBAR SHOW - HIDE
  // ========================================================================= //
  $(window).scroll(function() {
    var scroll = $(window).scrollTop();
    if (scroll > 200 ) {
      $("#main-nav, #main-nav-subpage").slideDown(700);
      $("#main-nav-subpage").removeClass('subpage-nav');
    } else {
      $("#main-nav").slideUp(700);
      $("#main-nav-subpage").hide();
      $("#main-nav-subpage").addClass('subpage-nav');
    }
  });

  // ========================================================================= //
  //  // RESPONSIVE MENU
  // ========================================================================= //
  $('.responsive').on('click', function(e) {
    $('.nav-menu').slideToggle();
  });

  // ========================================================================= //
  //  Typed Js
  // ========================================================================= //
  var typed = $(".typed");
  $(function() {
    typed.typed({
      strings: ["AAA Anaa Airport, French Polynesia",
                "LHR London Heathrow",
                "NHT RAF Northolt, London",
                "GIB Gibraltar Airport, Gibraltar",
                "GFT GeoffTech",
                "ZZV Zanesville Municipal Airport, USA"],
      typeSpeed: 120,
      loop: true,
    });
  });
});

