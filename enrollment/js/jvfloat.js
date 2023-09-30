/*
 * JVFloat.js
 * modified on: 18/09/2014
 */

(function($) {
  'use strict';
  
  // Init Plugin Functions
  $.fn.jvFloat = function () {
    // Check input type - filter submit buttons.
    return this.filter('input:not([type=submit]), textarea, select').each(function() {
      function getPlaceholderText($el) {
        var text = $el.data('placeholder');

        if (typeof text == 'undefined') {
            text = $el.attr('title');
        }

        return text;
      }
      function setState () {
        // change span.placeHolder to span.placeHolder.active
        var currentValue = $el.val();

        if (currentValue == null) {
          currentValue = '';
        }
        else if ($el.is('select')) {
          var placeholderValue = getPlaceholderText($el);

          if (placeholderValue == currentValue) {
            currentValue = '';
          }
        }

        //placeholder.toggleClass('active', currentValue !== '');
        
         var activate = (currentValue !== '');
        if (typeof $el.data('rawMaskFn') !== 'undefined') {
          //activate = true;
        }

        placeholder.toggleClass('active', activate);

        var fieldEl = placeholder.parent().children().eq(1);
        if (fieldEl.prop('nodeName') == 'INPUT' || fieldEl.prop('nodeName') == 'SELECT' || fieldEl.prop('nodeName') == 'TEXTAREA') {
          fieldEl.toggleClass('not-empty', activate);
          if (fieldEl.prop('nodeName') == 'SELECT') {
            
          }
        }
      }
      function minimizeLabelOnMaskedFields () {
        var fieldEl = placeholder.parent().children().eq(1);

        if (typeof $el.data('rawMaskFn') !== 'undefined' || fieldEl.prop('nodeName') == 'SELECT') {
          placeholder.toggleClass('active', true);
          if (fieldEl.prop('nodeName') == 'INPUT' || fieldEl.prop('nodeName') == 'SELECT' || fieldEl.prop('nodeName') == 'TEXTAREA') {
            fieldEl.toggleClass('not-empty', true);
          }
        }

        if (fieldEl.prop('nodeName') == 'SELECT') {
          fieldEl.focus();
          //var ev = jQuery.Event("keydown");
          //ev.which = 13;
          //fieldEl.trigger(ev);

          //var event;
          //event = document.createEvent('MouseEvents');
          //event.initMouseEvent('mousedown', true, true, window);
          //document.getElementById($el.attr('id')).dispatchEvent(event);
        }
      }
      function generateUIDNotMoreThan1million () {
        var id = '';
        do {
          id = ('0000' + (Math.random()*Math.pow(36,4) << 0).toString(36)).substr(-4);
        } while (!!$('#' + id).length);
        return id;
      }
      function createIdOnElement($el) {
        var id = generateUIDNotMoreThan1million();
        $el.prop('id', id);
        return id;
      }
      // Wrap the input in div.jvFloat
      var $el = $(this).wrap('<div class=jvFloat>');
      var forId = $el.attr('id');
      if (!forId) { forId = createIdOnElement($el);}
      // Store the placeholder text in span.placeHolder
      // added `required` input detection and state
      var required = $el.attr('required') || ''; 
      
      
      $el.data('placeholder', $el.attr('placeholder'));
      $el.attr('placeholder', '');
      
      // adds a different class tag for text areas (.jvFloat .placeHolder.textarea) 
      // to allow better positioning of the element for multiline text area inputs
      var placeholder = '';
      var placeholderText = getPlaceholderText($el);
         
      if(typeof placeholderText != 'undefined'){
        // add red colored (*) to placeholder   
        var patt = /\*/g;
        var result = patt.test(placeholderText);
        if(result){
          placeholderText = placeholderText.replace('*','') + '<span class="red">*</span>';
        }
        if ($(this).is('textarea')) {
          placeholder = $('<label class="placeHolder ' + ' textarea ' + required + '" for="' + forId + '">' + placeholderText + '</label>').click(minimizeLabelOnMaskedFields).insertBefore($el);
        } else {
          placeholder = $('<label class="placeHolder ' + required + '" for="' + forId + '">' + placeholderText + '</label>').click(minimizeLabelOnMaskedFields).insertBefore($el);
        }
        // checks to see if inputs are pre-populated and adds active to span.placeholder
        setState();
        //$el.bind('keyup blur', setState);
        $el.bind('mousedown keyup blur change', setState);
        $el.bind('click', minimizeLabelOnMaskedFields);
      }      
      
    });
  };
// Make Zeptojs & jQuery Compatible
})(window.jQuery || window.Zepto || window.$);
