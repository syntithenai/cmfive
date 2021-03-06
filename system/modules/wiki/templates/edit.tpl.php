        <?php echo Html::b(WEBROOT."/wiki/view/".$wikiname."/".$pagename,"View");?>
    
			<form action="<?php echo WEBROOT."/wiki/edit/".$wikiname."/".$pagename; ?>" method="POST">
                <input type="hidden" name="<?php echo CSRF::getTokenID(); ?>" value="<?php echo CSRF::getTokenValue(); ?>" />
				<textarea style="width:80%;height:500px;" name="body"><?php echo $page->body; ?></textarea>
			<p></p>
			<button type="submit" class="button tiny">Save</button>
			<?php echo Html::b(WEBROOT."/wiki/view/".$wikiname."/".$pagename,"Cancel"); ?> 
			<?php echo Html::box(WEBROOT."/wiki/markup","Markup Help",true); ?>
			<?php echo Html::box($webroot."/file/attach/WikiPage/".$page->id."/wiki+edit+".$wiki->name."+".$page->name,"Attach a File",true); ?>		
			</form>
			<p></p>			
			<?php if (!empty($attachments)): ?>
			<table class="tablesorter">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($attachments as $att): ?>
                <tr>
                    <td>
                    	<?php if ($att->isImage()): ?>
                    	<a href="<?php echo $webroot; ?>/file/atthumb/<?php echo $att->id; ?>/1024/768/a.jpg" rel="gallery">
                    	<?php else: ?>
                    	<a href="<?php echo $webroot; ?>/file/atfile/<?php echo $att->id; ?>/<?php echo $att->filename; ?>">
                    	<?php endif; ?>
                    	<?php echo $att->filename; ?></a>
                    </td>
                    <td>
                       	<?php echo $att->description; ?></a>
                    </td>                    
                    <td><?php echo Html::a($webroot."/file/atdel/".$att->id."/wiki+edit+".$wiki->name."+".$page->name,"Delete",null,null,"Do you want to delete this attachment?"); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
<script type="text/javascript">
    $("a[rel='gallery']").colorbox();
</script>     

