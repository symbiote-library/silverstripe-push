<div id="$ID" class="field push-provider" data-fields-link="$Link(fields)">
	<h2>$Title</h2>

	<% if Message %>
		<div class="message $MessageType">$Message</div>
	<% end_if %>

	<div class="provider">
		$ProviderField.FieldHolder
	</div>

	<div class="provider-fields">
		<% include PushProviderField_ProviderFields %>
	</div>
</div>
