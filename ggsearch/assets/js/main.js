"use strict";

//
// $('#element').donetyping(callback[, timeout=1000])
// Fires callback when a user has finished typing. This is determined by the time elapsed
// since the last keystroke and timeout parameter or the blur event--whichever comes first.
//   @callback: function to be called when even triggers
//   @timeout:  (default=1000) timeout, in ms, to to wait before triggering event if not
//              caused by blur.
// Requires jQuery 1.7+
// @link http://stackoverflow.com/questions/14042193/how-to-trigger-an-event-in-input-text-after-i-stop-typing-writing
;(function($){
    $.fn.extend({
        donetyping: function(callback,timeout){
            timeout = timeout || 1e3; // 1 second default timeout
            var timeoutReference,
                doneTyping = function(el){
                    if (!timeoutReference) return;
                    timeoutReference = null;
                    callback.call(el);
                };
            return this.each(function(i,el){
                var $el = $(el);
                // Chrome Fix (Use keyup over keypress to detect backspace)
                // thank you @palerdot
                $el.is(':input') && $el.on('keyup keypress',function(e){
                    // This catches the backspace button in chrome, but also prevents
                    // the event from triggering too premptively. Without this line,
                    // using tab/shift+tab will make the focused element fire the callback.
                    if (e.type=='keyup' && e.keyCode!=8) return;
                    
                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function(){
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                }).on('blur',function(){
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
            });
        }
    });
})(jQuery);

/**
 * Code-Port: "Hello {0}".format("World"); returns "Hello World"
 */
if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
} 

/**
 * Code-Port: Check if a string starts with a other string
 */
if (typeof String.prototype.startsWith != 'function') {
  String.prototype.startsWith = function (str){
    return this.indexOf(str) === 0;
  };
}

/**
 * Available Hooks (Parameters in args)
 * 
 * hide
 * show
 * changed (0 = Item selected [jQuery Object], 1 = Name [string])
 * input_changed
 * start_search (0 = Length of Input [int])
 * search_entries (0 = Entries loaded [JSON])
 * entries_found (0 = Entries loaded [JSON])
 * entries_not_found
 * click_extra_{name} (0 = Item clicked [jQuery Object], 1 = Name [string])
 * click_item_{name} (0 = Item clicked [jQuery Object], 1 = Name [string])
 * output_{name} (0 = args[0].rows with all Entries [JSON])
 * output_cpt (0 = args[0].rows with all Entries [JSON]) => For all Custom Post Types
 */
var GG_HOOK = { // source: Jon Hobbs @http://www.velvetcache.org/
  hooks: [],
  
  register: function ( name, callback ) {
    if( 'undefined' == typeof( GG_HOOK.hooks[name] ) )
      GG_HOOK.hooks[name] = [];
    GG_HOOK.hooks[name].push( callback );
  },

  call: function ( name, args ) {
    if( 'undefined' != typeof( GG_HOOK.hooks[name] ) )
      for(var i = 0; i < GG_HOOK.hooks[name].length; ++i )
        if( false == GG_HOOK.hooks[name][i]( GG_HOOK, args ) ) { break; }
  },
  
  exists: function(name) {
      return 'undefined' != typeof( GG_HOOK.hooks[name]);
  }
}

jQuery(document).ready(function($) {
    // Prepare available objects and values
    var container = $("#gg-search"),
        input = container.find("input"),
        rows = container.find(".rows"),
        toggleButton = $(".gg-toolbar-button"),
        extras = container.find(".gg-extras"),
        wpBody = $("#wpbody"),
        adminmenu = $("#adminmenu"),
        admintops = adminmenu.children(".menu-top"),
        ajaxRequest = null,
        actualValue = "";
    
    // Put the jQuery Objects in the Hook-System to use in external functions
    GG_HOOK.container = container;
    GG_HOOK.input = input;
    GG_HOOK.rows = rows;
    GG_HOOK.toggleButton = toggleButton;
    GG_HOOK.extras = extras;
    GG_HOOK.wpBody = wpBody;
    GG_HOOK.adminmenu = adminmenu;
    GG_HOOK.admintops = admintops;
    
    // By double tap the key G opens the search
    var typingTimer; // Keyup end writing input
    var doneTypingInterval = 500;
    var delta = 500; // Keyup double GG
    var lastKeypressTime = 0;
    
    input.keyup(function(){
        /* @deprecated
        clearTimeout(typingTimer);
        if (input.val) {
            typingTimer = setTimeout(gg_search_ajax, doneTypingInterval);
        }
        */
        GG_HOOK.call("input_changed", [ input.val() ]);
    });
    
    input.donetyping(function() {
        gg_search_ajax();
    });
    
    input.on("blur", function(e) {
        setTimeout(gg_search_hide, 100);
        //gg_search_hide();
        e.preventDefault();
    });
    
    function gg_search_hide() {
        GG_HOOK.call("hide");
    }
    
    function gg_search_show() {
        actualValue = "";
        GG_HOOK.call("show");
    }
    
    toggleButton.click(function(e) {
        if ($(this).hasClass("active")) {
            //gg_search_hide();
        }else{
            gg_search_show();
        }
        e.preventDefault();
        return false;
    });
    
    $(document).keyup(function(e) {
        switch(e.which) {
            // Do something by ENTER
            case 13:
                var active = rows.find(".gg-item.hover"),
                    href = active.attr("href"),
                    direct = true;
                if (active.hasClass("gg-extra")) {
                    var filter = active.attr("data-filter");
                    if (GG_HOOK.exists("click_extra_" + filter)) {
                        GG_HOOK.call("click_extra_" + filter, [ active, href ]);
                        direct = false;
                    }
                }else{
                    var name = active.attr("data-name");
                    if (GG_HOOK.exists("click_item_" + name)) {
                        GG_HOOK.call("click_item_" + name, [ active, href ]);
                        direct = false;
                    }
                }
                
                if (direct && container.is(":visible") && isDefined(href)) {
                    window.location.href = href;
                }
                return;
            
            // Do something by 2x G
            case 71:
                var active = $(document.activeElement), propName = active.prop("tagName");
                if (isDefined(active) && isDefined(propName)) {
                    if (propName == "INPUT" || propName == "TEXTAREA" || propName == "A") break;
                }
                
                var thisKeypressTime = new Date();
                if ( thisKeypressTime - lastKeypressTime <= delta ) {
                    gg_search_show();
                    thisKeypressTime = 0;
                }
                lastKeypressTime = thisKeypressTime;
                break;
            
            // Do something by [.]
            case 190:
                if (container.hasClass("gg-general-point")) {
                    var active = $(document.activeElement), propName = active.prop("tagName");
                    if (isDefined(active) && isDefined(propName)) {
                        if (propName == "INPUT" || propName == "TEXTAREA" || propName == "A") break;
                    }
                    
                    gg_search_show();
                }
                break;
            
            // Do something by arrow down/up
            case 40:
            case 38:
                gg_search_arrow(e.which);
                return;
            // Do something by ESC
            case 27:
                input.blur();
                gg_search_hide();
                return;
            default: break;
        }
        
        // Refresh extras
        var value = input.val();
        if (value.length > 0 && value.length != actualValue.length) {
            extras.find(".gg-extra.gg-item").each(function() {
                $(this).html($(this).attr("data-title").format(value));
                $(this).attr("href", $(this).attr("data-link").format(encodeURIComponent(value)));
            });
            
            rows.find(".gg-extra").remove();
            rows.append(extras.html());
        
            // Search left admin menu
            if (container.hasClass("gg-general-adminmenu")) {
                var sub, cnt;
                if (value.length > 0) {
                    value = value.toUpperCase();
                    admintops.each(function() {
                        sub = $(this).find("li:not(.wp-submenu-head) a");
                        cnt = 0;
                        if (sub.size() > 0) {
                            sub.each(function() {
                                if ($(this).html().toUpperCase().indexOf(value) > -1) {
                                    $(this).removeClass("wp-gg-hide");
                                    cnt++;
                                }else{
                                    $(this).addClass("wp-gg-hide");
                                }
                            });
                        }
                        
                        if (cnt > 0 || $(this).find("a .wp-menu-name").html().toUpperCase().indexOf(value) > -1) {
                            $(this).removeClass("wp-gg-hide");
                        }else{
                            $(this).addClass("wp-gg-hide");
                        }
                    });
                }else{
                    adminmenu.find(".wp-gg-hide").removeClass("wp-gg-hide");
                }
            }
        }
        actualValue = value;
        
        e.preventDefault();
    });
    
    $(document).on("mousedown", "#gg-search .gg-item", function(e) {
        window.location.href = $(this).attr("href");
        e.preventDefault();
        return false;
    });
    
    // Hover search element
    $(document).on("mouseenter", "#gg-search .gg-item", function() {
        container.find(".gg-item.hover").removeClass("hover");
        $(this).addClass("hover");
        GG_HOOK.call("changed", [ $(this), $(this).attr("data-name") ]);
    });
    
    function gg_search_arrow(keycode) {
        if (container.find(".gg-item").size() == 0) return;
        
        var active = container.find(".gg-item.hover").first(),
            activeIdx = rows.find(".gg-item").index(active),
            next = false;
        
        if (keycode == 40) {
            next = rows.find(".gg-item:visible:in-viewport:eq(" + (activeIdx + 1) + ")");
        }else{
            next = rows.find(".gg-item:visible:in-viewport:eq(" + (activeIdx - 1) + ")");
        }
        
        // Auswählen
        try {
            active.removeClass("hover");
            next.addClass("hover");
            GG_HOOK.call("changed", [ next, next.attr("data-name") ]);
        }catch(e) {}
    }
    
    function gg_search_ajax() {
        var value = input.val();
        if (value.length <= 2) {
            if (value.length == 0) rows.html("");
            container.removeClass("loading");
            return;
        }

        GG_HOOK.call("start_search", [ value.length ]);
        if (value.length > 0) {
            container.addClass("loading");
            
            var data = {
        		'action': 'gg_search_action',
        		'term': value
        	};
        	
        	if (ajaxRequest != null) {
        	    try {
        	        ajaxRequest.abort();
        	    }catch(e) {}
        	}
        	ajaxRequest = jQuery.ajax({
        	    type: "POST",
        	    url: ajaxurl,
        	    data: data,
        	    invokeData: { term: value.toUpperCase() },
        	    success: function(response) {
        	        if (this.invokeData.term != input.val().toUpperCase()) {
        	            return;
        	        }
            		response = jQuery.parseJSON(response);
            		var entries = response.entries;
            		var extraShow = response.extraShow;
            		
            		rows.html("");
            		var cnt = 0;
            		$.each(entries, function(key, value) {
            		    cnt += value.rows.length;
            		    
            		    if (GG_HOOK.exists("output_" + value.name)) {
            		        GG_HOOK.call("output_" + value.name, [ value ]);
            		    }else if (value.name.startsWith("cpt_") && GG_HOOK.exists("output_cpt")) {
            		        GG_HOOK.call("output_cpt", [ value ]);
            		    }else{
            		        gg_search_load_row(value);
            		    }
            		});
            		
            		if (cnt > 0) {
            		    var first = rows.find(".gg-item:first");
            		    first.addClass("hover");
            		    GG_HOOK.call("changed", [ first, first.attr("data-name") ]);
            		    GG_HOOK.call("entries_found", [ entries ]);
            		}else{
            		    GG_HOOK.call("entries_not_found", []);
            		}
            		
            		rows.append(extras.html());
            		$.each(extraShow, function(key, value) {
            		    rows.find(".gg-item.gg-condition.gg-extra.gg-extra-" + value).addClass("show");
            		});
            		
            		container.removeClass("loading");
            		GG_HOOK.call("search_entries", [ entries ]);
        	    }
        	});
        }else{
            rows.html("");
            actualValue = "";
        }
    }
    
    function gg_search_load_row(row) {
        $('<div class="gg-group gg-group-' + row.name + '">' + row.category + '</div>').appendTo(rows);
        $.each(row.rows, function(key, value) {
            $('<a href="' + value.link + '" class="gg-item gg-group-' + row.name + '" data-name="' + row.name + '">' + value.title + '</a>').appendTo(rows);
        });
    }
});

/**
 * Gets a Parameter from current URL
 * e. g. test.php?foo=bar => getParameterByName("foo") returns bar
 */
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

/**
 * Value not undefined
 */
function isDefined(attr) {
    if (typeof attr !== typeof undefined && attr !== false) {
        return true;
    }
    return false;
}