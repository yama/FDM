/**
 * fdmEform
 * 
 * サンプルテンプレート(FDM用)
 * 
 * @category	chunk
 * @version 	0.2
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 */
[+validationmessage+]
<form method="post" action="[~[*id*]~]" id="fdmForm">
<fieldset>
	<legend>ページ設定</legend>
		<input type="hidden" name="formid" value="fdmForm" />
		<input type="hidden" name="fdmid" value="[+fdmid+]" eform="::0::" />
	<p>
		<label for="pagetitle">タイトル</label><br />
		<input name="pagetitle" id="pagetitle" value="[+pagetitle+]" type="text" eform="タイトル:string:1" />
	</p>
	<p>
		<label>公開日:<br />
		<input name="pub_date" type="text" value="[+pub_date+]" size="30" readonly="readonly" />
		</label>
		<a onclick="nwpub_cal1.popup();" title="Choose the publish date">
		<img align="absmiddle" src="assets/snippets/FDM/images/cal.gif" border="0" alt="Select date" /></a>
		<a onclick="document.forms['fdmForm'].elements['pub_date'].value=''; return true;" >
		<img align="absmiddle" src="assets/snippets/FDM/images/event3.png" border="0" alt="Remove date"></a>
	</p>
</fieldset>
<fieldset>
	<legend>内容</legend>
	<p><label>本文<br />
		<textarea name="content" id="content" rows="5" cols="50" eform="Du texte:html:1">[+content+]</textarea>
		</label>
	</p>
</fieldset>
<p><input name="fdmsubmit" value="送信" type="submit" /></p>
</form>

<form id="abort" style="position:relative;top:-3em;left:5em;" method="post" action="[~[*id*]~]">
	<input type="submit" name="clickabort" value="キャンセル" />
</form>

<script language="JavaScript" src="assets/snippets/FDM/js/calendar/datefunctions.js"></script>
<script type="text/javascript">
	var elm_txt = {}; // dummy
	var pub = document.forms["fdmForm"].elements["pub_date"];
	var nwpub_cal1 = new calendar1(pub,elm_txt);
	nwpub_cal1.path="[(base_url)]assets/snippets/FDM/js/";
	nwpub_cal1.year_scroll = true;
	nwpub_cal1.time_comp = true;
</script>
