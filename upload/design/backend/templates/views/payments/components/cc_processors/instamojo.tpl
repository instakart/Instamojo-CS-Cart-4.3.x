<div class="control-group">
    <label class="control-label" for="instamojo_client_id">Client ID:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_client_id]" id="merchant_id" value="{$processor_params.instamojo_client_id}"   size="200">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_client_secret">Client Secret</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_client_secret]" id="password" value="{$processor_params.instamojo_client_secret}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_testmode">Test Mode:</label>
    <div class="controls">
        <select  value="{$processor_params.instamojo_testmode}" name="payment_data[processor_params][instamojo_testmode]" id="instamojo_testmode" >
			<option value='0'>No</option>
			<option {if $processor_params.instamojo_testmode eq "1"} selected="selected"{/if} value='1'>Yes</option>
			
		</select>
    </div>
</div>

