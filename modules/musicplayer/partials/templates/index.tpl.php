<div data-id='metadata' >
	<?php echo $metaFields ?>		
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
		<div class='searchresultsrowtemplate' style='display:none; border-bottom: 1px solid black'>
			<span class='buttonset' ><input type='submit' value ='Edit' data-action='edit'  class='button tiny' /><input  class='button tiny' type='submit' value ='Delete' data-action='delete' /></span>
			<span style='display:none' data-field='id'>1</span>
			<span data-field='title'>The title</span>
			<span data-field='data'>some data here</span>
		</div>
	</div>
</div>

<div data-id='editform' style='display:none'>
	<h3>Edit</h3>
	<form>
		<input type='hidden' data-role='editfield' data-field='id' />
		<input type='text'  data-role='editfield' data-field='title' />
		<input type='text'  data-role='editfield' data-field='data'/>
		<input type='hidden' data-role='hiddenfield' data-field='<?php echo $csrf_id; ?>' value="<?php echo $csrf_val; ?>" />
		<input type='submit' data-action='save' value='Save' class='button tiny'/><input type='submit' data-action='close' value='Cancel' class='button tiny'/>
	</form>
</div>
		
