"use strict";

jQuery(document).ready(function($) {
    var gg = GG_HOOK;
    
    gg.register("hide", function(objs) {
        objs.wpBody.removeClass("gg-search");
        objs.adminmenu.find(".wp-gg-hide").removeClass("wp-gg-hide");
        objs.container.removeClass("show");
        /*
        objs.container.stop().fadeOut(200);
        */
        objs.toggleButton.removeClass("active");
    });
    
    gg.register("show", function(objs) {
        objs.input.val("");
        objs.wpBody.toggleClass("gg-search");
        objs.container.addClass("show");
        objs.input.focus();
        /*
        objs.container.stop().slideToggle(200, function() {
             objs.input.focus();
        });
        */
        objs.toggleButton.addClass("active"); 
        $("#gg-search .gg-not-found").hide();
        objs.rows.html("");
    });
    
    /*
    HOOK Examples
    gg.register("changed", function(objs, args) {
        var item = args[0],
            groupName = args[1];
        alert(item.html());
        alert(groupName);
    });
    
    gg.register("click_item_post", function(objs, args) {
       alert(args[1]);
    });
    
    */
    
    gg.register("output_post", function(objs, args) {
        gg_cpt(objs, args);
    });
    gg.register("output_page", function(objs, args) {
        gg_cpt(objs, args);
    });
    gg.register("output_cpt", function(objs, args) {
        gg_cpt(objs, args);
    });
    
    function gg_cpt(objs, args) {
        var row = args[0], status;
        $('<div class="gg-group gg-group-' + row.name + '">' + row.category + '</div>').appendTo(objs.rows);
        $.each(row.rows, function(key, value) {
            status = (isDefined(value.output.status)) ? value.output.status : "";
            $('<a href="' + value.link + '" data-id="' + value.output.id + '" class="gg-item gg-cpt gg-group-' + row.name + '" data-name="' + row.name + '"><span>' + value.title + '</span><div class="right-text">' + status + '</div></a>').appendTo(objs.rows);
        });
    }
    
    gg.register("entries_not_found", function(objs, args) {
        $("#gg-search .gg-not-found").show();
    });
    
    gg.register("entries_found", function(objs, args) {
        $("#gg-search .gg-not-found").hide();
    });
    
    gg.register("input_changed", function(objs, args) {
        if (args[0].length <= 3) {
            $("#gg-search .gg-not-found").hide();
        }
    });
});