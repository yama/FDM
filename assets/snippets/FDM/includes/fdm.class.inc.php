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
	var $fdm_path;

	function CFDM()
	{
		global $modx;
		
		$fdm_path = strtr(realpath(dirname(__FILE__)), '\\', '/');
		$fdm_path = substr($fdm_path,0,strrpos($fdm_path,'includes'));
		if(!class_exists('fdmCChunkie'))
		{
			include_once($fdm_path . 'includes/fdmchunkie.class.inc.php');
		}
		$this->fdm_path = $fdm_path;
		$this->userT    = $modx->userLoggedIn();
		$this->ctime    = time();
	}
	
	function SetP($field, $value)
	{
		$this->parameters[$field] = $value;
	}
	
	function secure($value)
	{
		global $modx;
		// anti sql injection
		if(get_magic_quotes_gpc())
		{
			include_once $modx->config['base_path'] . 'manager/includes/quotes_stripper.inc.php';
			kill_magic_quotes($value);
		}
		$value = $modx->db->escape($value);
		return $value;
	}
	function Run()
	{
		global $modx;
		// General settings
		//include default language file include_once work only once :) so for multiple call : include
		include($this->fdm_path.'/lang/english.inc.php');
		//include other language file if set.
		$this->config['debug']=$_SESSION['fdm']['debug']= $this->parameters['debug'] ?$this->parameters['debug']: NULL;
		$this->config['language']	=$this->parameters['language'] ? $this->parameters['language']:$modx->config['manager_language'];
		if(file_exists($this->fdm_path.'/lang/'.$this->config['language'].'.inc.php'))
		{
			include ($this->fdm_path.'/lang/'.$this->config['language'].'.inc.php');
			$this->info['language']= 'File '.$this->fdm_path.'lang/'.$this->config['language'].'.inc.php'.'.inc.php found';
			$_SESSION['fdm']['language']=$this->config['language'];
		}
		else
		{
			$_SESSION['fdm']['language']='english';
			$this->info['language']= 'File '.$this->fdm_path.'lang/'.$this->config['language'].'.inc.php'.'.inc.php not found ! '.$this->fdm_path.'/lang/english.inc.php used.';
		}
		// Note: 0,'0','',NULL,array() are FALSE (http://www.php.net/manual/en/language.types.boolean.php)
		$this->config['action']=$_GET['fdmaction'] ? $this->secure($_GET['fdmaction']):'';
		$this->debug_run('Get parameters:',$_GET);
		if($this->parameters['id'])
		{
			$this->doc['id']=$this->parameters['id'];
		}
		else
		{
			if($this->parameters['editorpageid'])
			{
				die ('FDM: When you use editorpageid parameter, you must fill the \'id\' parameter in the snippet call !');
			}
			$this->doc['id']=$modx->documentIdentifier;
			$this->debug_run('No document id specified editing current doc !');
		}
		
		$tmpdoc=$modx->getPageInfo($this->doc['id'], 0,'id,parent,pagetitle,createdby,alias,published');
		$this->doc['parent']         = $this->parameters['parent'] ?$this->parameters['parent']: $tmpdoc['parent'];
		$this->doc['parent']         = $_GET['fdmparent']? $this->secure($_GET['fdmparent']):$this->doc['parent'] ;
		$this->doc['published']      = $tmpdoc['published'];//for publish button
		$this->doc['createdby']      = $tmpdoc['createdby'];
		$this->doc['model']          = $this->parameters['model']?$this->parameters['model']:0 ;
		$this->doc['model']          = $_GET['fdmmodel']? $this->secure($_GET['fdmmodel']):$this->doc['model'] ;
		$this->config['alias']       = $this->parameters['alias'] ? $this->parameters['alias']:'%pagetitle%';
		$this->config['script']      = $this->parameters['script'] ? $this->parameters['script'] :'';
		$this->config['realdelete']  = $this->parameters['fdmrealdelete'] ? $this->parameters['fdmrealdelete'] :0;
		$this->config['placeholder'] = $this->parameters['output']?$this->parameters['output']:1;
		$this->config['output']      = (isset($this->parameters['output'])) ?$this->parameters['output']:1;
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
		$this->link['editorpageid']=$modx->makeUrl($this->config['editorpageid'],'','','full');
		// link
		$this->link['uchar']=($modx->config['friendly_urls'] == 1 )?'?':'&';// first GET parameter char
		$this->link['current']=$modx->makeUrl($modx->documentIdentifier,'','','full') ;
		$this->link['parent']=$this->doc['parent']? str_replace('&amp;','&',$modx->makeUrl($this->doc['parent'],'','','full')):$modx->config['site_url'];
		$this->link['add'] =$this->link['editorpageid'].$this->link['uchar'].'fdmaction=add'.'&fdmmodel='.$this->doc['model'].'&fdmparent='.$this->doc['parent'].'&fdmid='.$this->doc['id'];
		$this->link['edit'] =$this->link['editorpageid'].$this->link['uchar'].'fdmaction=edit'.'&fdmid='.$this->doc['id'];
		$this->link['delete'] = str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=delete'.'&fdmid='.$this->doc['id'],'full'));
		$this->link['publish'] = str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=publish'.'&fdmid='.$this->doc['id'],'full'));
		$this->link['unpublish'] =str_replace('&amp;','&',$modx->makeUrl($this->config['editorpageid'],'','&fdmaction=unpublish'.'&fdmid='.$this->doc['id'],'full'));
		$this->templates['link']['tpl'] =  $this->parameters['tplLinks'] ? $this->parameters['tplLinks']:$_lang['links_template'];

// begin to work ----------------------------------------------
			$this->debug_run('doc id',$this->doc['id']);
		if( ($_SESSION['fdmid']==$this->doc['id'])&& $_POST['fdmsubmit']) // posted form (validated or not)
		{
			$this->OutputForm(); // eform job, thanks to him
		}
		elseif($_GET['fdmaction'] && ($_GET['fdmid']==$this->doc['id']))
		{
			//action :add edit (un)publish delete
			$this->debug_run('ACTION',"GET['fdmid']:{$_GET['fdmid']} / this->doc['id']{$this->doc['id']}/ GET['action']:{$_GET['fdmaction']}/ SESSION['fdmcpt']{$_SESSION['fdmcpt']}");
			$_SESSION['fdmid']=$this->doc['id'];
			$this->config['action']=$_GET['fdmaction'];
			if(!class_exists('FdmDocument')) include_once($this->fdm_path.'includes/fdm.db.class.inc.php');
			// common for edit & save action
			if( ($this->config['action']=='add') || ($this->config['action'] =='edit'))
			{
				if($this->config['script']) //load script dynamically (ie for tinymce)
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
				if( is_numeric($this->parameters['redirect']) )
				{
					$this->link['redirect'] = $modx->makeUrl($this->parameters['redirect'],'','','full');
				}
				elseif($this->parameters['redirect']=='parent')
				{
					$this->link['redirect'] = $modx->makeUrl($this->link['parent'],'','','full');
				}
				else
				{
					$this->link['redirect'] = 'current'; // for added document, we don't know its id, finding it later
				}
				$_SESSION['fdm']['redirect']=$this->link['redirect'] ;
			}

			switch ($this->config['action'])
			{
				case 'add':
					$this->debug_run('switch action','in add');
					if($this->user['canadd'])
					{
						$this->debug_run('switch action','in add and user can post');
						$_SESSION['fdm']['parent']=$this->doc['parent'];
						$_SESSION['fdm']['model']=$_SESSION['fdm']['source']=$this->doc['model'];
						$_SESSION['fdm']['action']='add';
						$this->debug_run('action:add before call outputform with:',$_SESSION['fdm']);
						$this->OutputForm();
					}
					break;
				case 'edit':
					if($this->user['canedit'])
					{
						$_SESSION['fdm']['anonymous']=0;
						$_SESSION['fdm']['id']=$this->doc['id'];
						$_SESSION['fdm']['source']=$this->doc['id'];
						$_SESSION['fdm']['action']='edit';
						$this->debug_run('action:edit before call outputform with:',$_SESSION['fdm']);
						$this->OutputForm();
					}
					break;
				case 'publish':
					if($this->user['ismoderator'])
					{
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->Publish($this->user['id']);
						$docObj->Save();
						header('Location: '.$this->link['current']);
					}
					break;
				case 'unpublish':
					if($this->user['ismoderator'])
					{
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->UnPublish($this->user['id']);
						$docObj->Save();
						$this->config['action']='none';
						//$docObj->flushcache();
						header('Location: '.$this->link['parent']);
					}
					break;
				case 'delete':
					if($this->user['ismoderator'] || $this->user['isauthor'])
					{
						$docObj = new FdmDocument($this->doc['id']);
						$docObj->Delete($this->user['id'],$this->config['realdelete']); // not save !!
						header('Location: '.$this->link['parent']);
					}
					break;
				default: break;
			}
// render the links action bar
		}
		else
		{
			$this->debug_run('no action, nor form, displaying the button bar');
			//$this->debug_run('user:',$this->user);
			if(!$_GET['fdmaction'] && !$_POST['fdmsubmit'])
			{
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

	function OutputForm()
	{
		// output the form for editing fields
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
		if(!function_exists('mergeFields'))
		{
			include_once($modx->config['base_path'].'assets/snippets/FDM/includes/fdm.functions.inc.php');
		}
		
 		$beforeFunc=(empty($_POST))?'mergeFields':''; // to keep the edited fields if there is a validation error
		// call to eForm
		$params['debug']                  = $this->eform['debug'];
		$params['formid']                 = $this->eform['formid'];
		// $params['eformOnBeforeFormParse'] = 'beforeparse';
		$params['eFormOnBeforeFormMerge'] = $beforeFunc;
		$params['eformOnBeforeMailSent']  = 'fillDoc';
		$params['eFormOnValidate']        = 'displayEformError';
		$params['eFormOnMailSent']        = 'allok';
		$params['protectSubmit']          = '1'; // else problem
		// $params['sessionVars']         =  'fdmid', //$_SESSION['fdmid'];
		// $params['postOverides']        = '0';
		$params['to']                     = $this->eform['to'];
		$params['tpl']                    = $this->eform['tpl'];
		$params['subject']                = $this->eform['subject'];
		$params['noemail']                = $this->eform['noemail'];
		$params['report']                 = $this->eform['report'];
		$params['thankyou']               = $this->eform['thankyou'];
		$params['allowhtml']              = '1';
		$params['sendAsText']             = '1';
		$params['autosender']             = $this->eform['autosender'];
		$eformOutput = $modx->runSnippet('eForm',$params);
		if($this->config['output'])
		{
			echo $eformOutput;
		}
		else
		{
			$modx->setPlaceholder('fdmEform',$eformOutput);
		}
	}
	
	function debug_run($str=' ',$value=' ')
	{
		if($_SESSION['fdm']['debug']!='run') return;
		if(is_array($value))
		{
			echo"<p style=\"color:#dd0055\">$str</p><div><pre><code>";
 			echo print_r($value)."</code></pre></div>\n";;
		}
		else
		{
			echo "<p style=\"color:#dd0055\"><strong>$str</strong> : $value</p>\n";
		}
	}
	
	function debug()
	{
		// For debugging ?
		//$this->info['template links']='<pre><code>'.str_replace('`','&quot;',htmlentities($this->templates['link']['tpl'])).'</pre></code>';
		switch ($this->config['debug'])
		{
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
}
