<?php

/*
 * Title: Language File
 * Purpose:
 *  	Japanese language file for FDM
 * Note:
 * 		New language keys should added at the bottom of this page
 * Version: 0.0.54
*/

$_lang['language'] = "japanese-utf8";

$_lang['abbr_lang'] = "ja";

$_lang['links_template']=<<<END
@CODE:
<span class="fdm-mod">

	[+user.canadd:is=`1`:and:if=`[+link.disableadd+]`:is=`0`:then=`
		<a href="[+link.add:esc+]" title="新規作成"><img src="[(site_url)]manager/media/style/MODxCarbon/images/icons/folder_page_add.png" alt="新規作成" border="0" /></a>
	`:strip+]

	[+link.disableedit:is=`0`:then=`
		[+user.ismoderator:is=`1`:or:if=`[+user.isauthor+]`:is=`1`:then=`
		<a href="[+link.edit:esc+]" title="編集"><img src="[(site_url)]manager/media/style/MODxCarbon/images/icons/logging.gif" alt="編集 " border="0" /></a>
		`:strip+]
	`+]
[+user.ismoderator:is=`1`:then=`
	[+doc.published:is=`0`:and:if=`[+link.disablepublish+]`:is=`0`:then=`
		<a href="[+link.publish:esc+]" onclick="return confirm('このドキュメントを公開してもよいですか?')" title="公開"><img src="[(site_url)]manager/media/style/MODxCarbon/images/icons/add.png" alt="公開 " border="0" /></a>
	`:strip+]
	[+doc.published:is=`1`:and:if=`[+link.disableunpublish+]`:is=`0`:then=`
		<a href="[+link.unpublish:esc+]" title="非公開"><img src="[(site_url)]manager/media/style/MODxCarbon/images/icons/delete.png" alt="非公開" border="0" /></a>
	`:strip+]
	[+link.disabledelete:is=`0`:then=`
		<a href="[+link.delete:esc+]" onclick="return confirm('このドキュメントを削除してもよいですか?')" title="削除"><img src="[(site_url)]manager/media/style/MODxCarbon/images/icons/event3.png" alt="削除" border="0" /></a>
	`:strip+]
`+]
</span>
<br style="clear:both;" />
END;

$_lang['empty_required_fields'] = '<h3>必須項目が入力されていません:</h3>';
$_lang['invalid_fields'] = '<h3>いくつかの項目の入力値が不正です:</h3>';
$_lang['doclink'] = '<p>ドキュメントへのリンク: <a title ="ドキュメントへ" href="[+url+]">[+pagetitle+]</a></p>';

