<?php
	// used for multiple call per page, don't redeclare the belowed functions
	function debug_run($str=' ',$value=' ')
	{
		if($_SESSION['fdm']['debug']!='run') return;
		if(is_array($value))
		{
			echo '<p style="color:#dd0055">' . $str . '</p><div><pre><code>';
 			echo print_r($value)."</code></pre></div>\n";;
		}
		else
		{
			echo '<p style="color:#dd0055"><strong>' . $str . '</strong> : ' . $value . "</p>\n";
		}
	}
	function beforeparse (&$templates)
	{
		//echo print_r ($templates);
	}
	function mergeFields(&$fields)
	{
		// fill the form with the content of the document
		global $modx;
		debug_run('enter mergeFields -fields:',$fields);
		//debug_run('mergeFields -sessions:',$_SESSION['fdm']);
		if($_SESSION['fdm']['model']==0 && ($_SESSION['fdm']['action']=='add'))
		{
			//debug_run('in mergeFields','model=0 action=add -> return');
			return $fields;
		}
		if(!class_exists('FdmDocument'))
		{
			include_once($modx->config['base_path'].'assets/snippets/FDM/includes/fdm.db.class.inc.php');
		}
		$docObj = new FdmDocument($_SESSION['fdm']['source']);
		$fields=array_merge($fields,$docObj->Gets());
		unset($docObj);
		// comment modx tags
		if(isset($fields['content']))
		{
			//$fields['content']=htmlentities ($fields['content'],ENT_QUOTES);
			$s=array ('[[','[!','[*','[+','[~','[(','{{','}}',')]',']]','!]','*]','+]','~]');
			$r=array ('<!-- MODx Uncached Snippet ', '<!-- MODx Cached Snippet ','<!-- MODx Doc field ','<!-- MODx Placeholder ','<!-- MODx Link ','<!-- MODx Config ','<!-- MODx Chunk ',' END MODx Chunk -->',' END MODx Config -->',' END MODx Uncached Snippet -->', ' END MODx Cached Snippet -->',' END MODx Doc field -->',' END MODx Placeholder -->',' END MODx Link -->');
			$fields['content']=str_replace($s,$r,$fields['content']);
			//debug_run('end mergefield-fields',$fields);
		}
		if($_SESSION['fdm']['action']=='add')
		{
			$fields['pagetitle']='';
		}
		return $fields;
	}
	function fillDoc(&$fields) {
		// Fill the document with the validated content of the form
		global $modx;
		if(!class_exists('FdmDocument'))
		{
			include_once($modx->config['base_path'].'assets/snippets/FDM/includes/fdm.db.class.inc.php');
		}
		debug_run('enter filldoc - fields:',$fields );
		$docObj = new FdmDocument($_SESSION['fdm']['source']);
		if($_SESSION['fdm']['action']=='add')
		{
			$docObj->Duplicate($_SESSION['fdm']['userid'],$_SESSION['fdm']['parent']);
			$docObj->Set('parent',$_SESSION['fdm']['parent']);
			$docObj->Set('createdby',$_SESSION['fdm']['userid']);
			//debug_run('in filldoc:','action add, parent:'.$_SESSION['fdm']['parent'].' createdby:'.$_SESSION['fdm']['userid'] );
		}
		// uncomment modx tags
		if(isset($fields['content']))
		{
			// $fields['content']=html_entity_decode ($fields['content'],ENT_QUOTES);
			debug_run('in filldoc:','content: <pre>'.$fields['content'].' </pre>');
			$r=array ('[[','[!','[*','[+','[~','[(','{{','}}',')]',']]','!]','*]','+]','~]');
			$s=array ('<!-- MODx Uncached Snippet ', '<!-- MODx Cached Snippet ','<!-- MODx Doc field ','<!-- MODx Placeholder ','<!-- MODx Link ','<!-- MODx Config ','<!-- MODx Chunk ',' END MODx Chunk -->',' END MODx Config -->',' END MODx Uncached Snippet -->', ' END MODx Cached Snippet -->',' END MODx Doc field -->',' END MODx Placeholder -->',' END MODx Link -->');
			$fields['content']=str_replace($s,$r,$fields['content']);
		}
		foreach ($fields as $key=>$val)
		{
			$docObj->Set($key,$val);// set posted fields in the doc
		}
		
		$docObj->Set('published',$_SESSION['fdm']['topublish']);
// NEW
		$newalias=$_SESSION['fdm']['aliastype']; // ie:%pagetitle_le%d-%m-%y
		$datefield=$_SESSION['fdm']['aliasdate']; // ie:editedon ,createdon, publishedon or deletedon
		preg_match_all('/%[^%]+%/',$newalias,$out);
		if(is_array($out))
		{
			foreach ($out[0] as $word)
			{
				if(strlen($word)>3)
				{
					$rest = substr($word, 1, strlen($word)-2);
					$val= $docObj->Get($rest);
					$newalias=str_replace($word,$val, $newalias);
				}
				else
				{
					$rest = substr($word, 0, strlen($word)-1);
					$val=strftime ( $rest  ,$datefield);
					$newalias=str_replace($word,$val, $newalias);
				}
			}
		}
		else
		{
			die ('FDM: You have an error in the aliastype parameter.');
		}
		$newalias=$modx->stripAlias($newalias);
		debug_run('Alias:',$_SESSION['fdm']['aliastype'].' : '.$newalias );
		if(!$newalias)
		{
			// for new document not saved, with default alias, id don't exist
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
	
	function displayEformError(&$fields,&$vMsg,&$rMsg)
	{
		// Display validation and required fields errors
		include_once('assets/snippets/FDM/lang/'.$_SESSION['fdm']['language'].'.inc.php');
		$_SESSION['fdmcpt']=0;
		if(!empty($vMsg))
		{
			echo $_lang['invalid_fields'];
			foreach ($vMsg as $validedMsg)
			{
				echo '<p class="error">'.$validedMsg.'</p>'."\n";
			}
		}
		if(!empty($rMsg))
		{
			echo $_lang['empty_required_fields'];
			foreach ($rMsg as $requiredMsg)
			{
				echo '<p class="error">'.$requiredMsg.'</p>'."\n";
			}
		}
	}
	function allok(&$fields)
	{
		// The document is ok and saved redirect the user to the correct page
		//echo '<h3>dans allok:'.'</h3>';echo print_r($_SESSION['fdm']);
		unset($_SESSION['action_processed']);
		$_SESSION['fdmcpt']=0;
		include('assets/snippets/FDM/lang/'.$_SESSION['fdm']['language'].'.inc.php');
		debug_run('enter allok - session:',$_SESSION['fdm'] );
		if($_SESSION['fdm']['redirect']=='current')
		{
			$link=str_replace('[+url+]',$_SESSION['fdm']['url'],$_lang['doclink']);
			$link=str_replace('[+pagetitle+]',$_SESSION['fdm']['pagetitle'],$link);
			echo $link;
			$url=$_SESSION['fdm']['url'];
			unset($_SESSION['fdm']);
			unset($_SESSION['fdmid']);
			header('Location: '.$url);
		}
		else
		{
			$url =$_SESSION['fdm']['redirect'];
			unset($_SESSION['fdm']);
			unset($_SESSION['fdmid']);
			header('Location: '.$url);
		}
	}
