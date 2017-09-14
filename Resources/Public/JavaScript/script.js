jQuery(function () {
    /* <![CDATA[ */
    // initialize dialog box
    jQuery("#dialogHint").dialog({
        autoOpen: false,
        height: 150,
        width: 300,
        modal: true
    });

    // show dialog box on click
    jQuery("span.csh")
      .css("cursor", "pointer")
      .click(function() {
          var id = jQuery(this).nextAll(":input:not(:hidden)").attr("id");
          jQuery("#dialogHint p").text(jQuery("div.hidden_" + id).text());
          jQuery("#dialogHint").dialog("open");
      });
    /* ]]> */
});