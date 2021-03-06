<div class="tabs">
    <div class="tab-head">
        <a href="#details">Task Details</a>
        <a href="#timelog">Time Log</a>
        <a href="#comments">Comments</a>
        <a href="#documents">Documents</a>
       	<?php if ($task->getCanINotify()):?><a href="#notification">Notifications</a><?php endif;?>
    </div>	
	
    <div class="tab-body">
        <div id="details" class="clearfix">
            <?php echo !empty($btndelete) ? $btndelete : null; ?>
            <?php echo !empty($btntimelog) ? $btntimelog : null; ?>
            <?php $tasktypeobject = $task->getTaskTypeObject(); !empty($tasktypeobject) ? $tasktypeobject->displayExtraButtons($task) : null;?>

            <?php echo !empty($viewtask) ? $viewtask : null; ?>
            <?php echo !empty($extradetails) ? $extradetails : null; ?>
        </div>
        <div id="timelog">
            <?php echo !empty($addtime) ? $addtime : null; ?>
            <?php echo !empty($timelog) ? $timelog : null; ?>
        </div>
        <div id="comments">
            <?php echo $w->partial("listcomments",array("object"=>$task,"redirect"=>"task/viewtask/{$task->id}#comments"), "admin"); ?>
        </div>
        <div id="documents">
            <?php echo $w->partial("listattachments",array("object"=>$task,"redirect"=>"task/viewtask/{$task->id}#documents"), "file"); ?>
        </div>
        <?php if ($task->getCanINotify()):?>
        <div id="notification" class="clearfix">
            Set your Notifications specific to this Task, otherwise your notifications for this Task Group will be employed.
            <?php echo $tasknotify;?>
        </div>
        <?php endif;?>
    </div>
</div>

<script language="javascript">

	$(".startTime").click(function(e){
    	var url = $(this).attr("href");
    	var screenW = screen.width;
    	var x = screenW - 360;
    	var t = 0; 	
        var winName = "Task Time Log";
    	var winParameters = "width=360,height=300,scrollbars=no,toolbar=no,status=no,menubar=no,location=no";

    	var thiscookie = getCookie("thiswin");
    	
		if (!thiscookie) {
	    	thiswin = window.open(url, winName, winParameters);
			thiswin.moveTo(x,t);
			thiswin.focus();
		}
		else {
			alert("Please END TIME on your current Task" + "\n" +  "before starting a new Task Time Log");

			if (typeof(thiswin) != "undefined" && !thiswin.closed)
				thiswin.focus();
		}

        e.preventDefault();
     });

function getCookie(cname) {
    var cVal = null;
    if(document.cookie) {
        var arr = document.cookie.split((escape(cname) + '=')); 
        if(arr.length >= 2) {
            var arr2 = arr[1].split(';');
            cVal  = unescape(arr2[0]);
        }
    }
    return cVal;
}
</script>
