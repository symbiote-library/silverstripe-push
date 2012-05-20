(function($) {
	$(".push-provider .provider select").live("change", function() {
		var field  = $(this).closest(".push-provider");
		var fields = field.find(".provider-fields").empty();
		var link   = field.attr("data-fields-link");

		if(this.value) {
			fields.addClass("loading").load(link + "/" + this.value, function() {
				fields.removeClass("loading");
			});
		}
	});
})(jQuery);
