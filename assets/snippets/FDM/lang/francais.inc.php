<?php

/*
 * Title: user French Language File
 * About: Default English language file for user.
 * Author: David Piaser
 * Note: New language keys should added at the bottom of this page
 * Version: 0.0.53
 */

$_lang['language'] = "french";

$_lang['abbr_lang'] = "fr";

$_lang['links_template']=<<<END
@CODE:

<span class="fdm-mod">

	[+user.canadd:is=`1`:and:if=`[+link.disableadd+]`:is=`0`:then=`
		<a href="[+link.add:esc+]" onclick="return confirm('Etes vous sûr de vouloir ajouter un document?')" title="Ajouter un Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/folder_page_add.gif" width="18" height="18" alt="| Nouveau |" border="0" /></a>
	`:strip+]

	[+link.disableedit:is=`0`:then=`
		[+user.ismoderator:is=`1`:or:if=`[+user.isauthor+]`:is=`1`:then=`
		<a href="[+link.edit:esc+]" onclick="return confirm('Etes vous sûr de vouloir éditer ce document?')" title="Editer le Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/logging.gif" width="16" height="16" alt="Editer |" border="0" /></a>
		`:strip+]
	`+]
[+user.ismoderator:is=`1`:then=`
	[+doc.published:is=`0`:and:if=`[+link.disablepublish+]`:is=`0`:then=`
		<a href="[+link.publish:esc+]" onclick="return confirm('Etes vous sûr de vouloir publier le document ?')" title="Publier le Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/add.png" width="16" height="16" alt="Publish |" border="0" /></a>
	`:strip+]
	[+doc.published:is=`1`:and:if=`[+link.disableunpublish+]`:is=`0`:then=`
		<a href="[+link.unpublish:esc+]" onclick="return confirm('Etes vous sûr de vouloir dépublier le document ?')" title="Dépublier le  Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/delete.png" width="16" height="16" alt="Dépublier |" border="0" /></a>
	`:strip+]
	[+link.disabledelete:is=`0`:then=`
		<a href="[+link.delete:esc+]" onclick="return confirm('Etes vous sûr de vouloir effacer ce Document?')" title="Effacer le Document"><img src="[(base_url)]manager/media/style/MODx/images/icons/event3.gif" width="16" height="16" alt="Effacer |" border="0" /></a>
	`:strip+]
`+]
</span>
<br style="clear:both;"/>
END;



$_lang['empty_required_fields'] = '<h3>Certains champs requis sont vides:</h3>';
$_lang['invalid_fields'] = '<h3>Certains champs sont invalides:</h3>';
$_lang['doclink'] = '<p>Lien vers le document : <a title ="accéder au document" href="[+url+]">[+pagetitle+]</a></p>';

?>
