<div data-id='metadata' >
<?php 
	$metaTags='';
	foreach ($meta as $k =>$v) {
		echo '<input type="hidden" data-metadata="'.$k.'" value="'.$v.'"  />'."\n";
	}
?>		
</div>

<div data-id='search' style='display:none;' >
	<div>Search supports * wildcards and space seperated tokens acting as logical AND</div>
			
	<div data-id='querybuilder'></div>
	<div data-id='searchform' class='row-fluid' >
		<form >
			<input type='text' data-id='searchinput' value=''/>
			<span class='buttonset' ><input type='submit' data-action="search" value='Search' class='button tiny'/><input type='submit' data-action="advancedsearch" value='Advanced Search' class='button tiny'/><input type='submit' class='newbutton button tiny buttonset' value='New'/></span>
		</form>
	</div>
	
	<div  data-id='searchresults' style='display:none; border: 1px solid black' >
		  <?php echo $w->partial("radsearchresults",array('classname'=>$meta['className']), "rad"); ?>
	</div>

</div>

<div data-id='editform' style='display:none'>
	<?php echo $w->partial("radeditform",array('classname'=>$meta['className']), "rad"); ?>
</div>
		
