<?php
/*
 * Class CFDM
 * Class for MODx front end editing
 * Version: 0.1b
 * Author: David (aka Soda) (soda@dp-site.fr)
 * Author: yama yamamoto@kyms.ne.jp
 * Date: Avril 07, 2007 09:43 CET
 * Documentation, Changelog, Thanks ... are in the FDM_documentation.html file.
 * Support forum: http://modxcms.com/forums/index.php/topic,14977.0.html  Read the doc before posting your problem !
 * Free
 */


class CFDM {
	var $config = array();
	var $eFormParameters = array();
	var $user=array();
	var $doc=array();
	var $ctime;
	var $templates = array();


	function CFDM() {
		global $modx;
		$path = strtr(realpath(dirname(__FILE__)), '\\', '/');
		$path=str_replace('includes', '', $path);
		if (!class_exists('fdmCChunkie'))
			include_once($path . 'includes/fdmchunkie.class.inc.php');
		$this->path=$path;
		$this->client = $modx->getUserData();
		$this->userT=$modx->userLoggedIn();
		$this->ctime = time();
	}
	function SetP($field, $value) {
		$this->parameters[$field] = $value;
	}
	function secure($value) {// anti sql injection
		$value=(get_magic_quotes_gpc())?stripslashes($value):$value;
		$value = mysql_real_escape_string($value);
		return $value;
	}
	function Run() {
		global $modx;
// General settings
		//include default language file include_once work only once :) so for multiple call : include
		include($this->path.'/lang/english.inc.php');
		//include other language file if set.
		$this->config['debug']=$_SESSION['fdm']['debug']= $this->parameters['debug'] ?$this->parameters['debug']: NULL;
		$this->config['language']	=$this->parameters['language'] ? $this->parameters['language']:$modx->config['manager_language'];
		if (file_exists($this->path.'/lang/'.$this->config['language'].'.inc.php')) {
			include ($this->path.'/lang/'.$this->config['language'].'.inc.php');
			$this->info['language']= 'File '.$this->path.'lang/'.$this->config['language'].'.inc.php'.'.inc.php found';
			$_SESSION['fdm']['language']=$this->config['language'];
		}else{
			$_SESSION['fdm']['language']='english';
			$this->info['language']= 'File '.$this->path.'lang/'.$this->config['language'].'.inc.php'.'.inc.php not found ! '.$this->path.'/lang/english.inc.php used.';
		}

// Note: 0,'0','',NULL,array() are FALSE (http://www.php.net/manual/en/language.types.boolean.php)
		$this->config['action']=$_GET['fdmaction'] ? $this->secure($_GET['fdmaction']):'';
		$this->debug_run('Get parameters:',$_GET);
		if ($this->parameters['id']){
			$this->doc['id']=$this->parameters['id'];
		} else {
			if ($this->parameters['editorpageid'])
			{
				die ('FDM: When you use editorpageid parameter, you must fill the \'id\' parameter in the snippet call !');
			}
			$this->doc['id']=$modx->documentIdentifier;
			$this->debug_run('No document id specified editing current doc !');
			}

		$tmpdoc=$modx->getPageInfo($this->doc['id'], 0,'id,parent,pagetitle,createdby,alias,published');
		$this->doc['parent']=$this->parameters['parent'] ?$this->parameters['parent']: $tmpdoc['parent'];
		$this->doc['parent'] = $_GET['fdmparent']? $this->secure($_GET['fdmparent']):$this->doc['parent'] ;
		$this->doc['published']=$tmpdoc['published'];//for publish button
		$this->doc['createdby'] =$tmpdoc['createdby'];
		$this->doc['model'] = $this->parameters['model']?$this->parameters['model']:0 ;
		$this->doc['model'] = $_GET['fdmmodel']? $this->secure($_GET['fdmmodel']):$this->doc['model'] ;
		$this->config['alias']= $this->parameters['alias'] ? $this->parameters['alias']:'%pagetitle%';
		$this->config['script'] = $this->parameters['script'] ? $this->parameters['script'] :'';
		$this->config['realdelete'] = $this->parameters['fdmrealdelete'] ? $this->parameters['fdmrealdelete'] :0;

		$this->config['placeholder']=$this->parameters['output']?$this->parameters['output']:1;
		$this->config['output']=(isset($this->parameters['output'])) ?$this->parameters['output']:1;
//Alias
		$this->config['aliastype']=$_SESSION['fdm']['aliastype']=$this->parameters['aliastype']?$this->parameters['aliastype']:'%id%';
		$this->config['aliasdate']=$_SESSION['fdm']['aliasdate']=$this->parameters['aliasdate']?$this->parameters['aliasdate']:'createdon';

//disable button ?
		$this->link['disableadd']	=$this->parameters['disableadd'] ?$this->parameters['disableadd']:0;
		$this->link['disableedit']	=$this->parameters['disableedit'] ?$this->parameters['disableedit']:0;
		$this->link['disablepublish']	=$this->parameters['disablepublish'] ? $this->parameters['disablepublish']:0;
		$this->link['disableunpublish']	=$this->parameters['disableunpublish'] ?$this->parameters['disableunpublish']:0;
		$this->link['disabledelete']	=$this->parameters['disabledelete'] ? $this->parameters['disabledelete']:0;
// Group
		$this->config['permissions']['post'] = $this->parameters['canpost'] ? explode(',',$this->parameters['canpost']):array();
		$this->config['permissions']['moderate'] = $this->parameters['canmoderate'] ? explode(',',$this->parameters['canmoderate']):0;
		$this->config['permissions']['trusted'] = $this->parameters['trusted'] ? explode(',',$this->parameters['trusted']):array();
   		$this->config['moderation']['disablemanager'] = $this->parameters['disablemanager'] ? $this->parameters['disablemanager']:0;
   		$this->config['moderation']['moderated'] = $this->parameters['moderated'] ?  $this->parameters['moderated']:0;
		$this->config['permissions']['trusted']=(!$this->config['moderation']['moderated'])? array_merge($this->config['permissions']['trusted'],$this->config['permissions']['post']):$this->config['permissions']['trusted'];
		$this->config['anonymousallowed']=$this->parameters['anonymous'] ? $this->parameters['anonymous']:0;
// user
		$this->user['id']=($_SESSION['mgrInternalKey'] && !$_SESSION['webInternalKey'])?$_SESSION['mgrInternalKey']:0;
		$this->user['id']=$_SESSION['webInternalKey']?-$_SESSION['webInternalKey']:$this->user['id'];
		$this->user['ismanager'] = ($_SESSION['mgrInternalKey'] && !$this->parameters['disablemanager'])?1:0;
		$this->user['isanonymous'] = ($this->user['id'])?0:1;
		$this->user['isauthor'] = (($this->user['id']==$this->doc['createdby']) && !$this->user['isanonymous'])?1:0;
		$this->user['ismoderator'] =($modx->isMemberOfWebGroup($this->config['permissions']['moderate'] ) || $this->user['ismanager'])?1:0;
		$this->user['istrusted'] = $modx->isMemberOfWebGroup($this->config['permissions']['trusted'] );
		$this->user['isposter'] = $modx->isMemberOfWebGroup($this->config['permissions']['post'] );
// user rights
		$this->user['canadd'] = ($this->user['ismoderator']||$this->user['istrusted']||$this->user['isposter'] || ($this->user['isanonymous'] && $this->config['anonymousallowed'])) ? 1 :0;
		$this->user['canedit'] =($this->user['ismoderator'] || $this->user['isauthor']) ? 1 :0;
		$this->user['canpub'] = $this->user['candelete'] = $this->user['ismoderator'] ;
		$this->config['topublish']=($this->user['ismoderator']||$this->user['istrusted'])?1:0;
// edit on an other page
		$this->config['editorpageid']=$this->parameters['editorpageid'] ? $this->parameters['editorpageid'] :$modx->documentIdentifier;
		$this->link['editorpageid']=$modx->makeUrl($this->config['editorpageid']);
// link
		$this->link['uchar']=($modx->config['friendly_urls'] == 1 )?'?':'&';// first GET parameter char
		$this->link['current']=$modx->makeUrl($modx->documentIdentifier) ;
		$this->link['parent']=$this->doc['parent']? str_replace('&amp;','&',$modx->makeUrl($this->doc['parent'])):$modx->config['site_url'];
		$this->link['add'] =$this->link['editorpageid'].$this->link['uchar'].'fdmaction=add'.'&fdmmodel='.$this->doc['model'].'&fdmparent='.$this->doc['parent'].'&fdmid='.$this->doc['id'];
		$this->link['edit'] =$this->link['editorpageid'].$this->link['uchar'].'fdmaction=edit'.'&fdmid='.$this->doc['id'];
		$this->link['delete'] = str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=delete'.'&fdmid='.$this->doc['id']));
		$this->link['publish'] = str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=publish'.'&fdmid='.$this->doc['id']));
		$this->link['unpublish'] =str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=unpublish'.'&fdmid='.$this->doc['id']));
		$this->templates['link']['tpl'] =  $this->parameters['tplLinks'] ? $this->parameters['tplLinks']:$_lang['links_template'];

// begin to work ----------------------------------------------
			$this->debug_run('doc id',$this->doc['id']);
		if ( ($_SESSION['fdmid']==$this->doc['id'])&& $_POST['fdmsubmit']) // posted form (validated or not)
		{
			$this->OutputForm(); // eform job, thanks to him
		} elseif ($_GET['fdmaction'] && ($_GET['fdmid']==$this->doc['id'])){ //action :add edit (un)publish delete
			$this->debug_run('ACTION',"GET['fdmid']:".$_GET['fdmid']." / this->doc['id']".$this->doc['id']."/ GET['action']:".$_GET['fdmaction']."/ SESSION['fdmcpt']".$_SESSION['fdmcpt']);
			$_SESSION['fdmid']=$this->doc['id'];
			$this->config['action']=$_GET['fdmaction'];
			if (!class_exists('FdmDocument')) include_once($this->path.'includes/fdm.db.class.inc.php');
			// common for edit & save action
			if ( ($this->config['action']=='add') || ($this->config['action'] =='edit'))
			{
				if ($this->config['script']) //load script dynamically (ie for tinymce)
					{
						$this->debug_run('script ','posted script:'.$this->config['script']);
						$tpl = new fdmCChunkie($this->config['script']);
						$tpl->AddVar('doc',$this->doc);// you can access doc placeholder in you script to pass parameter to tinymce plugin
						$links[] = $tpl->Render();
						$output_links = join('',$links);
						$modx->regClientStartupScript($output_links);
					}
				$_SESSION['fdm']['editorid']=$this->config['editorpageid'];
				$_SESSION['fdm']['userid']=$this->user['id'];
				$_SESSION['fdm']['topublish']=$this->config['topublish'];
				if( is_numeric($this->parameters['redirect']) ){
							$this->link['redirect'] = $modx->makeUrl($this->parameters['redirect']);
				}else if ($this->parameters['redirect']=='parent'){
							$this->link['redirect'] = $modx->makeUrl($this->link['parent']);
				}else {
							$this->link['redirect'] = 'current'; // for added document, we don't know its id, finding it later
				}
				$_SESSION['fdm']['redirect']=$this->link['redirect'] ;
			}

			switch ($this->config['action']) {

				case 'add':
					$this->debug_run('switch action','in add');
					if ($this->user['canadd'] ){
						$this->debug_run('switch action','in add and user can post');
						$_SESSION['fdm']['parent']=$this->doc['parent'];
						$_SESSION['fdm']['model']=$_SESSION['fdm']['source']=$this->doc['model'];
						$_SESSION['fdm']['action']='add';
						$this->debug_run('action:add before call outputform with:',$_SESSION['fdm']);
						$this->OutputForm();
					}
					break;
				case 'edit':
					if ($this->user['canedit']){
 						$_SESSION['fdm']['anonymous']=0;
						$_SESSION['fdm']['id']=$this->doc['id'];
						$_SESSION['fdm']['source']=$this->doc['id'];
						$_SESSION['fdm']['action']='edit';
						$this->debug_run('action:edit before call outputform with:',$_SESSION['fdm']);
						$this->OutputForm();
					}
					break;

				case 'publish':
					if ($this->user['ismoderator']){
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->Publish($this->user['id']);
						$docObj->Save();
						header('Location: '.$this->link['current']);
					}
					break;

				case 'unpublish':
					if ($this->user['ismoderator']){
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->UnPublish($this->user['id']);
						$docObj->Save();
						$this->config['action']='none';
						//$docObj->flushcache();
						header('Location: '.$this->link['parent']);
					}
					break;
				case 'delete':
					if ($this->user['ismoderator'] || $this->user['isauthor']){
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->Delete($this->user['id'],$this->config['realdelete']); // not save !!
						header('Location: '.$this->link['parent']);
					}
					break;

//				case 'none':
				default: break;
			}
// render the links action bar
		} else {
			$this->debug_run('no action, nor form, displaying the button bar');
			//$this->debug_run('user:',$this->user);

			if (!$_GET['fdmaction'] && !$_POST['fdmsubmit']){
				$tpl = new fdmCChunkie($this->templates['link']['tpl']);
				$tpl->AddVar('user',$this->user);
				$tpl->AddVar('link',$this->link);
				$tpl->AddVar('doc',$this->doc);
				$links[] = $tpl->Render();
				$output_links = join('',$links);
				echo $output_links;
				//unset($_SESSION['fdm']);
			}
		}
	}


	function OutputForm() { // output the form for editing fields
		global $modx;
		$_SESSION['fdmcpt']=0;
		// eForm var
		$this->eform['tpl'] =$this->parameters['eform_tpl'] ? $this->parameters['eform_tpl'] :'fdmEform';
		$this->eform['formid'] = $this->parameters['eform_formid'] ? $this->parameters['eform_formid'] : 'fdmForm';
		$this->eform['report'] =  $this->parameters['eform_report'] ? $this->parameters['eform_report'] : '';
		$this->eform['thankyou'] =  $this->parameters['eform_thankyou'] ? $this->parameters['eform_thankyou'] : '';
		$this->eform['to'] =  $this->parameters['eform_to'] ? $this->parameters['eform_to'] : $modx->config['emailsender'];
		$this->eform['noemail'] = isset($this->parameters['eform_noemail']) ? $this->parameters['eform_noemail'] : true;
		$this->eform['subject'] = $this->parameters['eform_subject'] ? $this->parameters['eform_modified subject'] : 'FDM: notification '.$modx->config['site_url'];
		$this->eform['autosender'] =  $this->parameters['eform_autosender'] ? $this->parameters['eform_autosender'] : NULL;
		$this->eform['debug'] =$this->parameters['eform_debug'] ? $this->parameters['eform_debug'] : 0;
		if (!function_exists('mergeFields')){ // used for multiple call per page, don't redeclare the belowed functions
			function debug_run($str=' ',$value=' ') {
				if ($_SESSION['fdm']['debug']!='run') return;
				if (is_array($value) ){
					echo"<p style=\"color:#dd0055\">$str</p><div><pre><code>";
		 			echo print_r($value)."</code></pre></div>\n";;
 				} else {
						echo "<p style=\"color:#dd0055\"><strong>$str</strong> : $value</p>\n";
				}
			}
		function beforeparse (&$templates) {
//echo print_r ($templates);
		}

		function mergeFields(&$fields) {// fill the form with the content of the document
			global $modx;
			debug_run('enter mergeFields -fields:',$fields);
			//debug_run('mergeFields -sessions:',$_SESSION['fdm']);
			if ($_SESSION['fdm']['model']==0 && ($_SESSION['fdm']['action']=='add')) {
			//debug_run('in mergeFields','model=0 action=add -> return');
				return $fields;
		}

	  	if (!class_exists('FdmDocument')) include_once($modx->config['base_path'].'assets/snippets/FDM/includes/fdm.db.class.inc.php');
			$docObj = new FdmDocument($_SESSION['fdm']['source']);
			$fields=array_merge($fields,$docObj->Gets());
			unset($docObj);
			// comment modx tags
			if (isset($fields['content'])){
				//$fields['content']=htmlentities ($fields['content'],ENT_QUOTES);
				$s=array ('[[','[!','[*','[+','[~','[(','{{','}}',')]',']]','!]','*]','+]','~]');
				$r=array ('<!-- MODx Uncached Snippet ', '<!-- MODx Cached Snippet ','<!-- MODx Doc field ','<!-- MODx Placeholder ','<!-- MODx Link ','<!-- MODx Config ','<!-- MODx Chunk ',' END MODx Chunk -->',' END MODx Config -->',' END MODx Uncached Snippet -->', ' END MODx Cached Snippet -->',' END MODx Doc field -->',' END MODx Placeholder -->',' END MODx Link -->');
				$fields['content']=str_replace($s,$r,$fields['content']);
				//debug_run('end mergefield-fields',$fields);
			}
			if ($_SESSION['fdm']['action']=='add')
			{
				$fields['pagetitle']='';
			}
			return $fields;
		}

		function fillDoc(&$fields) {// Fill the document with the validated content of the form
			global $modx;
			if (!class_exists('FdmDocument')) include_once($modx->config['base_path'].'assets/snippets/FDM/includes/fdm.db.class.inc.php');
			debug_run('enter filldoc - fields:',$fields );
			$docObj = new FdmDocument($_SESSION['fdm']['source']);
			if ($_SESSION['fdm']['action']=='add' ){
				$docObj->Duplicate($_SESSION['fdm']['userid'],$_SESSION['fdm']['parent']);
				$docObj->Set('parent',$_SESSION['fdm']['parent']);
				$docObj->Set('createdby',$_SESSION['fdm']['userid']);
				//debug_run('in filldoc:','action add, parent:'.$_SESSION['fdm']['parent'].' createdby:'.$_SESSION['fdm']['userid'] );
			}
			// uncomment modx tags
			if (isset($fields['content'])){
			//	$fields['content']=html_entity_decode ($fields['content'],ENT_QUOTES);
				debug_run('in filldoc:','content: <pre>'.$fields['content'].' </pre>');
				$r=array ('[[','[!','[*','[+','[~','[(','{{','}}',')]',']]','!]','*]','+]','~]');
				$s=array ('<!-- MODx Uncached Snippet ', '<!-- MODx Cached Snippet ','<!-- MODx Doc field ','<!-- MODx Placeholder ','<!-- MODx Link ','<!-- MODx Config ','<!-- MODx Chunk ',' END MODx Chunk -->',' END MODx Config -->',' END MODx Uncached Snippet -->', ' END MODx Cached Snippet -->',' END MODx Doc field -->',' END MODx Placeholder -->',' END MODx Link -->');
				$fields['content']=str_replace($s,$r,$fields['content']);
			}

			foreach ($fields as $key=>$val){
				$docObj->Set($key,$val);// set posted fields in the doc
			}

			$docObj->Set('published',$_SESSION['fdm']['topublish']);
// NEW
			$newalias=$_SESSION['fdm']['aliastype']; // ie:%pagetitle_le%d-%m-%y
			$datefield=$_SESSION['fdm']['aliasdate']; // ie:editedon ,createdon, publishedon or deletedon
			preg_match_all('/%[^%]+%/',$newalias,$out);
			if (is_array($out))
			{
				foreach ($out[0] as $word){
					if (strlen($word)>3)
					{
						$rest = substr($word, 1, strlen($word)-2);
						$val= $docObj->Get($rest);
						$newalias=str_replace($word,$val, $newalias);
					} else {
						$rest = substr($word, 0, strlen($word)-1);
						$val=strftime ( $rest  ,$datefield);
						$newalias=str_replace($word,$val, $newalias);
					}
				}
			} else { die ('FDM: You have an error in the aliastype parameter.');}
			$newalias=$modx->stripAlias($newalias);
			debug_run('Alias:',$_SESSION['fdm']['aliastype'].' : '.$newalias );
			if (!$newalias){ // for new document not saved, with default alias, id don't exist
				$docObj->Save();
				$newalias=$docObj->Get('id');
			}

			$docObj->Set('alias',$newalias);
			$docObj->Save();
			$_SESSION['fdm']['id']=$docObj->Get('id'); // store the new created document id (add)
			$_SESSION['fdm']['parent']=$docObj->Get('parent');
			$_SESSION['fdm']['pagetitle']=$docObj->Get('pagetitle');
			$_SESSION['fdm']['url']=$docObj->makeUrl();

			//echo '<h3>dans filldoc ap save:'.'</h3>';echo print_r($_SESSION['fdm']);
			$docObj->flushcache();
			//debug_run('end filldoc: -Fields:',$fields);
			//debug_run('end filldoc - docObj:',$docObj->Fields);
			//debug_run('end filldoc - session:',$_SESSION['fdm']);
 			unset($docObj);

		}

		function displayEformError(&$fields,&$vMsg,&$rMsg){	// Display validation and required fields errors
			include_once('assets/snippets/FDM/lang/'.$_SESSION['fdm']['language'].'.inc.php');
			$_SESSION['fdmcpt']=0;
			if (!empty($vMsg)){
				echo $_lang['invalid_fields'];
				foreach ($vMsg as $validedMsg){
					echo '<p class="error">'.$validedMsg.'</p>'."\n";
				}
			}
			if (!empty($rMsg)){
				echo $_lang['empty_required_fields'];
				foreach ($rMsg as $requiredMsg){
					echo '<p class="error">'.$requiredMsg.'</p>'."\n";
				}
			}
		}
		function allok(&$fields){// The document is ok and saved redirect the user to the correct page
			//echo '<h3>dans allok:'.'</h3>';echo print_r($_SESSION['fdm']);
			unset($_SESSION['action_processed']);
			$_SESSION['fdmcpt']=0;
			include('assets/snippets/FDM/lang/'.$_SESSION['fdm']['language'].'.inc.php');
			debug_run('enter allok - session:',$_SESSION['fdm'] );
			if ($_SESSION['fdm']['redirect']=='current'){
				$link=str_replace('[+url+]',$_SESSION['fdm']['url'],$_lang['doclink']);
				$link=str_replace('[+pagetitle+]',$_SESSION['fdm']['pagetitle'],$link);
				echo $link;
				$url=$_SESSION['fdm']['url'];
				unset($_SESSION['fdm']);
				unset($_SESSION['fdmid']);
				header('Location: '.$url);
			} else {
				$url =$_SESSION['fdm']['redirect'];
				unset($_SESSION['fdm']);
				unset($_SESSION['fdmid']);
				header('Location: '.$url);
			}

		}
}//FIN FUNCTION EXISTS

 		$beforeFunc=(empty($_POST))?'mergeFields':''; // to keep the edited fields if there is a validation error
		// call to eForm
		$eformOutput = $modx->runSnippet('eform',
			array(
				'debug'=>$this->eform['debug'],
				'formid'=>$this->eform['formid'],
				//'eformOnBeforeFormParse'=>'beforeparse',
				'eFormOnBeforeFormMerge'=>$beforeFunc,
				'eformOnBeforeMailSent'=>'fillDoc',
				'eFormOnValidate'=>'displayEformError',
				'eFormOnMailSent'=>'allok',
				'protectSubmit'=>'1',  // else problem
				//'sessionVars'=> 'fdmid', //$_SESSION['fdmid'],
				//'postOverides'=>'0',
				'to'=>$this->eform['to'],
				'tpl'=>$this->eform['tpl'],
				'subject'=>$this->eform['subject'],
				'noemail'=>$this->eform['noemail'],
				'report'=>$this->eform['report'],
				'thankyou'=>$this->eform['thankyou'],
				'allowhtml'=>'1',
				'sendAsText'=>'1',
				'autosender'=>$this->eform['autosender'])
			);
		if ($this->config['output'])
		{
			echo $eformOutput;
		}else {
			$modx->setPlaceholder('fdmEform',$eformOutput);
		}
	}


				function debug_run($str=' ',$value=' ') {
				if ($_SESSION['fdm']['debug']!='run') return;
				if (is_array($value)){
					echo"<p style=\"color:#dd0055\">$str</p><div><pre><code>";
		 			echo print_r($value)."</code></pre></div>\n";;
 				} else {
						echo "<p style=\"color:#dd0055\"><strong>$str</strong> : $value</p>\n";
				}
			}

	function debug()
	{ 	// For debugging ?
		//$this->info['template links']='<pre><code>'.str_replace('`','&quot;',htmlentities($this->templates['link']['tpl'])).'</pre></code>';
		switch ($this->config['debug']) {
				case 'all':echo'<h4>debug all</h4>';
						echo '<pre>';echo print_r($this);echo'</pre></div>';
						break;
				case 'lang':
					echo'<h3>lang file</h3>';
					echo '<pre>';echo print_r($lang);echo'</pre></div>';
					break;
				case 'user':
					echo'<h3>User variables</h3>';
					echo '<pre>permissions:';echo print_r($this->config['permissions']);echo'</pre>';
					echo '<pre>moderations:';echo print_r($this->config['moderation']);echo'</pre>';
					echo '<pre>user:';echo print_r($this->user);echo'</pre></div>';
					break;
				case 'param':
					echo'<h3>Parameters</h3>';
					echo '<pre>config:';echo print_r($this->parameters);echo'</pre></div>';
					break;
				case 'doc':
					echo'<h3>Document variables</h3>';
					echo '<pre>doc:';echo print_r($this->doc);echo'</pre>';
					echo '<p>IsAuthor: '.$this->user['isauthor'].'</p>';
					break;
		}
	}
/*
	function mb_str_ireplace($search, $replace, $str)
	{
    $strL = mb_strtolower($str);
    $searchL    = mb_strtolower($search);
    $offset = 0;

    while(($pos = mb_strpos($strL, $searchL, $offset)) !== false)
    {
        $offset = $pos + mb_strlen($replace);
        $str = mb_substr($str, 0, $pos). $replace .mb_substr($str, $pos+mb_strlen($search));
        $strL = mb_strtolower($str);
    }
     return $str;
	}

	function mb_str_replace($search, $replace, $str)
	{
    $offset = 0;
    while(($pos = mb_strpos($str, $search, $offset)) !== false)
    {
        $offset = $pos + mb_strlen($replace);
        $str = mb_substr($str, 0, $pos). $replace .mb_substr($str, $pos+mb_strlen($search));
    }
     return $str;
	}
*/
}
