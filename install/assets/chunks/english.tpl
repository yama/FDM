/**
 * fdmEform
 * 
 * eForm Template for editing Documents
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 */
[+validationmessage+]
<form method="post" action="[~[*id*]~]"  id="fdmForm">
<fieldset>
	<legend>Renseignements complémentaires</legend>
		<input type="hidden" name="formid" value="fdmForm" />
		<input type="hidden" name="fdmid" value="[+fdmid+]" eform="::0::" />
	<p>
		<label for="pagetitle">Pagetitle</label><br />
		<input name="pagetitle" id="pagetitle" value="[+pagetitle+]" type="text" eform="Title of the page:string:1" />
	</p>


 <p>
 	<label for="pub_date">Publish Date:</label><br />
  <input name="pub_date" id="pub_date" type="text" value="[+pub_date+]" size="30" readonly="readonly" />
        <a onclick="nwpub_cal1.popup();" title="Choose the publish date">
		<img align="absmiddle" src="manager/media/calendar/img/cal.gif" width="16" height="16" border="0" alt="Select date" /></a>
        <a onclick="document.forms['fdmForm'].elements['pub_date'].value=''; return true;" >
		<img align="absmiddle" src="manager/media/style/MODx/images/icons/event3.gif" width="16" height="16" border="0" alt="Remove date"></a>
	</p>
</fieldset>

<fieldset>
	<legend>Contenus</legend>
	<p>	<label for="content">Content of the page</label><br />
			<textarea name="content" id="content" rows="5" cols="50"  eform="Du texte:html:1">[+content+]</textarea>
	</p>
	<p>
			<label for="tvblogContent">Blog Content</label><br />
			<textarea name="tvblogContent" id="tvblogContent"  rows="5" cols="50"   type="text" eform="Enter the content of the blog:string:1" />[+tvblogContent+]</textarea>
	</p>
</fieldset>

<p><input name="fdmsubmit" value="Submit" type="submit" />  <a href="[~[*id*]~]" tile="Abort operation">Abort</a></p>
</form>

<form id="abort" style="position:relative;top:-3em;left:20em;" method="post" action="[~[*id*]~]">
	<input type="submit" name="clickabort" value="Abort" />
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
