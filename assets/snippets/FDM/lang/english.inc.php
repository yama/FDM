<?php

/*
 * Title: Language File
 * Purpose:
 *  	Default English language file for FDM
 * 		Author: David Piaser
 * Note:
 * 		New language keys should added at the bottom of this page
 * Version: 0.0.53
*/

$_lang['language'] = "english";

$_lang['abbr_lang'] = "en";

$_lang['links_template']=<<<END
@CODE:
<span class="fdm-mod">

	[+user.canadd:is=`1`:and:if=`[+link.disableadd+]`:is=`0`:then=`
		<a href="[+link.add:esc+]" onclick="return confirm('Are you sure you wish to add a document?')" title="Add Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/folder_page_add.gif" width="18" height="18" alt="New Document" border="0" /></a>
	`:strip+]

	[+link.disableedit:is=`0`:then=`
		[+user.ismoderator:is=`1`:or:if=`[+user.isauthor+]`:is=`1`:then=`
		<a href="[+link.edit:esc+]" onclick="return confirm('Are you sure you wish to edit this Document?')" title="Edit Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/logging.gif" width="16" height="16" alt="Edit " border="0" /></a>
		`:strip+]
	`+]
[+user.ismoderator:is=`1`:then=`
	[+doc.published:is=`0`:and:if=`[+link.disablepublish+]`:is=`0`:then=`
		<a href="[+link.publish:esc+]" onclick="return confirm('Are you sure you wish to publish this Document?')" title="Publish Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/add.png" width="16" height="16" alt="Publish " border="0" /></a>
	`:strip+]
	[+doc.published:is=`1`:and:if=`[+link.disableunpublish+]`:is=`0`:then=`
		<a href="[+link.unpublish:esc+]" onclick="return confirm('Are you sure you wish to unpublish this Document?')" title="Unpublish Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/delete.png" width="16" height="16" alt="Unpublish " border="0" /></a>
	`:strip+]
	[+link.disabledelete:is=`0`:then=`
		<a href="[+link.delete:esc+]" onclick="return confirm('Are you sure you wish to delete this Document?')" title="Delete Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/event3.gif" width="16" height="16" alt="Delete " border="0" /></a>
	`:strip+]
`+]
</span>
<br style="clear:both;"/>
END;




$_lang['empty_required_fields'] = '<h3>There is empty required field:</h3>';
$_lang['invalid_fields'] = '<h3>Some fields are not valid:</h3>';
$_lang['doclink'] = '<p>Link to the document : <a title ="to your document" href="[+url+]">[+pagetitle+]</a></p>';

?>
