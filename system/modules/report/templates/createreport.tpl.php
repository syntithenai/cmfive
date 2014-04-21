<div class="tabs">

    <div class="tab-head">
        <a href="#details">Report Details</a>
        <a href="#tables">View Database</a>
    </div>	
	<div class="tab-body">
		<div id="details">
			Please review the <b>Help</b> file for full instructions on the special syntax used to create reports.
			<p>
           <?php echo $createreport; ?>
        </div>
		<div id="tables" style="display: none;">
			<?php echo $dbform; ?>
			<p>
        </div>
   </div>
</div>

<script language="javascript">
	$.ajaxSetup ({
	    cache: false
		});

	var report_url = "/report/taskAjaxSelectbyTable?id="; 
	$("select[id='dbtables'] option").click(function() {
		$.getJSON(
			report_url + $(this).val(),
			function(result) {
				$('#dbfields').html(result);
				}
			);
		}
	);
</script>
