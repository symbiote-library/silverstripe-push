(function($) {
	$("#Provider-Settings-Subject").entwine({
		onadd: function() {
			if(!this.val()) this.val($("#Form_ItemEditForm_Title").val());
		}
	});

	$("#right input[name=action_doSend]").live("click", function() {
		var form    = $("#right form");
		var action  = form.attr("action") + "?" + $(this).fieldSerialize();
		var message = ss.i18n._t("Push.CONFIRMSEND", "Are you sure you want to send this push notification?");

		if(!confirm(message)) {
			return false;
		}

		$.ajax({
			type: "POST",
			url: action,
			data: form.formToArray(),
			complete: function(xhr, status) {
				if(status == "success") {
					statusMessage(xhr.statusText, "good");
				} else {
					errorMessage(xhr.statusText);
				}

				$('#right #ModelAdminPanel').html(xhr.responseText);

				Behaviour.apply();
				window.onresize();
			}
		});
	});
})(jQuery);
