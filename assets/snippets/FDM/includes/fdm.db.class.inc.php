<?php

/***************************************************************
  Name: FdmDocmanager
  Description: Class for editing/creating/duplicating/deleting FdmDocuments
  Based on the work of ur001,DocumentManager
  Version 0.5.4m modified for Front-End Document Manager (FDM) by Soda
  Author: ur001
  Author: yama yamamoto@kyms.ne.jp
  e-mail: ur001@mail.ru
**************************************************************/

class FdmDocument{
	var $fields;	// doc fields array
	var $tvs;		// TV array
	var $tvNames;	// TV names array
	var $oldTVs;	// TV values array
	var $isNew;		// true - new doc, false - existing doc

	/***********************************************
	  Initializing class
	  $id   - existing doc id or 0 for new doc
	  $fields - comma delimited field list
	************************************************/
	function FdmDocument($id=0,$fields="*"){
		global $modx;
		$this->isNew = $id==0;
		if(!$this->isNew){
			$this->fields = $modx->getPageInfo($id,0,$fields);
			$this->fields['id']=$id;
		} else {
			$this->fields = array(
				'type' =>'document',
				'contentType' =>'text/html',
				'pagetitle'	=> ' ',
				'longtitle'	=> '',
				'description' => '',
				'alias'		=> '',
				'link_attributes' => '',
				'published' =>'0',
				'pub_date' =>'0',
 				'unpub_date' =>'0',
				'parent'	=> 0,
				'isfolder' =>'0',
				'introtext' =>'',
				'content' 	=> '',
				'richtext' =>'0',
				'template' => $modx->config['default_template'],
				'menuindex' =>'0',
				'searchable' => $modx->config['search_default'],
				'cacheable'=> $modx->config['cache_default'],
				'createdby'=>'0',
				'createdon'=> time(),
				'editedby' =>'0',
				'editedon'=>'0',
				'deleted'=> '0',
				'deletedon'=> '0',
				'deletedby'=> '0',
				'publishedon'=> '0',
				'publishedby'=> '0',
				'menutitle'=> '',
				'donthit'=> '0',
				'haskeywords'=> '0',
				'hasmetatags'=> '0',
				'privateweb'=> '0',
				'privatemgr'=> '0',
				'content_dispo'=> '0',
				'hidemenu'=> '0'
			);
		}
	}


	function SetA($tabFieldValue){
		foreach 	($tabFieldValue as $field=> $value){
			$this->Set($field,$value);
		}
	}


	/***********************************************
	  Receiving doc values or TV
	  $field - doc value or TV with 'tv' prefix
	  Result: doc value, TV or null
	************************************************/
	function Get($field){
		switch(1){
			case substr($field,0,2)=='tv': return $this->GetTV(substr($field,2));
			default: return isset($this->fields[$field]) ? $this->fields[$field] : null;
		}
	}


	/***********************************************
	  Receiving TV
	  $name - TV name
	************************************************/
	function GetTV($tv){
		if(!is_array($this->tvs)){
			if($this->isNew) return null;
			$this->tvs=array();
		}
		if(isset($this->tvs[$tv])) return $this->tvs[$tv];
		if(!is_array($this->oldTVs)){
			if($this->isNew) return null;
			$this->oldTVs=$this->fillOldTVValues();
		}
		if(isset($this->oldTVs[$tv])) return $this->oldTVs[$tv];
		return null;
	}

	/****************************************************************
	  Result: doc value and TV in array tv with 'tv' before key name
	*****************************************************************/

	function Gets(){
		if (!is_array ($this->oldTVs)) $this->oldTVs=$this->fillOldTVValues();
		$tvs=is_array($tv)?$tvs:array();
		if (is_array ($this->oldTVs)){
			foreach ($this->oldTVs as $k=>$v){
				$t='tv'.$k;
  			$tvs[$t]=$v;
			}
		}
		return array_merge ($tvs, $this->fields);
	}

		function Affiche($t=''){
			echo"\n<h3>FdmDocument - $t -</h3>\n<pre>\n";
			echo htmlentities(print_r($this));
			echo"\n</pre>\n";
	}
	/***********************************************
	  Setting doc or TV value
	  $field - doc or TV (with prefix 'tv') name
	  $value - value
	  Result: true or false
	  Added: Set only if fields exist
	************************************************/
	function Set($field, $value){
		global $modx;
		switch(1){
			case $field=='id':return false;
			case substr($field,0,2)=='tv':		return $this->SetTV(substr($field,2), $value);
			case $field=='template':		return $this->SetTemplate($value);
			default:
			if (array_key_exists($field, $this->fields)){
				return $this->fields[$field]=$value;
			}
			return false;
		}
	}
	function SetAlias($alias=''){
		global $modx;
		$alias=$alias?$alias:$this->fields['pagetitle'];
		$alias= $modx->stripAlias($alias);
		return $alias;
	}

	function makeUrl(){
		global $modx;
		$siteUrl='';//str_replace ('/assets/','',$modx->config['rb_base_url']);
		if ($modx->config['friendly_urls'] == 1 )
		{
			$docalias= $modx->config['friendly_url_prefix'] . $this->fields['alias'] . $modx->config['friendly_url_suffix'];
			if ($modx->config['friendly_alias_urls'] == 1)
			{
				// $tpath=array_keys($modx->getParentIds($this->fields['id'])); // grrrr don't work
				$path = MODX_SITE_URL . $this->getPath();
			}
			$relUrl=$path.'/'.$docalias;
			$url= $siteUrl.str_replace ('//','/',$relUrl);
		} else {
				$url= 'index.php?id=' . $this->fields['id'] ;
		}
	return $url;
	}

	function getPath(){
		global $modx;
		$id2=$this->fields['parent'];
		$cpt=0;
		while ($parent!=0 || $cpt<10){
			$result = $modx->db->select('alias,parent', $modx->getFullTableName('site_content'), "id='$id2'");
   		if($modx->db->getRecordCount( $result ) >= 1 ) {
				$row = $modx->db->getRow( $result );
				$tpath[]=$row['alias'];
				$id2=$parent=$row['parent'];
			}
		$cpt+=1;
		}
		if (is_array($tpath)) return join('/',array_reverse($tpath)); else return '';

	}

	/***********************************************
	  Setting TV value
	  Added: Set only if Tv is available
	************************************************/
	function SetTV($tv,$value){
		if(!is_array($this->tvs)) $this->tvs=array();
		if(!is_array($this->tvNames)) $this->fillTVNames();

		// http://modxcms.com/forums/index.php/topic,14977.msg108314.html#msg108314 by swit4er

		if (isset($this->tvNames[$tv]))
		{
			if (is_array($value)) {
			$this->tvs[$tv]=implode("||",$value);
			}
			else {$this->tvs[$tv]=$value;}
		}
	}


	/***********************************************
	  Setting doc template
	  $tpl - template name or id
	************************************************/
	function SetTemplate($tpl=''){
		global $modx;
		// on  0.9.6 default template is set to 1 in system_setting, but minimal template has id 3 .Reselect it in config !
//		if (!$tpl) $tpl=$modx->config['default_template'];
		if(!is_numeric($tpl)) {
			$tablename=$modx->getFullTableName('site_templates');
			$tpl = $modx->db->getValue("SELECT id FROM $tablename WHERE templatename='$tpl' LIMIT 1");
			if(empty($tpl)) return false;
		}
		$this->fields['template']=$tpl;
		return true;
	}


	/************************************************************
	  Duplicatig doc with TVs
	  Added: User
	*************************************************************/
	function Duplicate($user='0',$parent='0'){
		global $modx;
		//$oid=$this->Get('id');
    //$pid=$this->Get('parent');

		if($this->isNew) return;

		$all_tvs=$this->fillOldTVValues();
		foreach($all_tvs as $tv=>$value)
			if(!isset($this->tvs[$tv])) $this->tvs[$tv]=$value;
		$this->oldTVs=array();
		$this->isNew=true;
		unset($this->fields['id']);

		if ($parent) $this->fields['parent']=$parent;
		if ($user)   $this->fields['createdby']=$user;
    $this->fields['createdon']=time();

	}

 	/***************************************************
	  Saving/Updating FdmDocument (escape fields added)
	****************************************************/
	function Save(){ //escape added
		global $modx;
		$this->fields['editedon']=time();
		$tablename=$modx->getFullTableName('site_content');
	  	$escapedFields = array_map(create_function('$n', 'global $modx;if (get_magic_quotes_gpc()) { $n = stripslashes($n);	}return $modx->db->escape($n);'), $this->fields);
		if($this->isNew){
			$this->fields['id']=$modx->db->insert($escapedFields, $tablename);
			$this->isNew = false;
		} else {
			$id=$this->fields['id'];
			$modx->db->update($escapedFields, $tablename, "id=$id");
		}
		unset($escapedFields);// free the array
		if(is_array($this->tvs)) $this->saveTVs();
	}

	/************************************************************
	  Saving TV values, maintenance function. Only $tvNames values are saved,
        If a TV exists in oldTVs, then updating, else inserting
	*************************************************************/
	function saveTVs(){
		global $modx;
		if(!is_array($this->tvNames))$this->fillTVNames();
		if(!is_array($this->oldTVs) && !$this->isNew)
			$this->oldTVs=$this->fillOldTVValues();
//		else 							//http://modxcms.com/forums/index.php/topic,6334.msg92335.html#msg92335
//			$this->oldTVs = array();
		elseif ( !is_array( $this->oldTVs ) )
    	$this->oldTVs = array();
		$tvc = $modx->getFullTableName('site_tmplvar_contentvalues');
		$id=$this->fields['id'];
		foreach($this->tvs as $tv=>$value){
			if (get_magic_quotes_gpc()) { // anti sql injection
  				$value = stripslashes($value);
			}
			$escapedValue= $modx->db->escape($value); // sql escape the value
			if(isset($this->tvNames[$tv])){
				$tmplvarid=$this->tvNames[$tv];
				if(isset($this->oldTVs[$tv])){
					if($this->oldTVs[$tv]==$this->tvNames[$tv]) continue;
					$sql="UPDATE $tvc SET value='$escapedValue' WHERE tmplvarid=$tmplvarid AND contentid=$id";
				}else{
					$sql="INSERT INTO $tvc (tmplvarid,value,contentid) VALUES ($tmplvarid,'$escapedValue',$id)";
				}
			$modx->db->query($sql);
			}
		}
	}


	function Delete($user='',$real=false){
		if($this->isNew) return;
		global $modx;
		$pid=$this->fields['parent'];
		$id=$this->fields['id'];
		if ($real){
			$id=$this->fields['id'];
			$modx->db->delete($modx->getFullTableName('site_content'),"id=$id");
			$modx->db->delete($modx->getFullTableName('site_tmplvar_contentvalues'),"contentid=$id");
			// add gallery ?
			$this->isNew=true;
		} else {
			$this->Set('deleted','1');
			$this->Set('deletedon',time());
			$this->Set('deletedby',$user);
			$this->Save();
		}
		return ($pid);
	}

	function Publish($user=''){
		global $modx;
		$this->Set('published',1);
		$this->Set('publishedon',time());
		$this->Set('publishedby',$user);
		$this->Save();
		$pid=$this->Get('parent');
		$id=$this->Get('id');
    }
	function UnPublish($user=''){
		global $modx;
		$this->Set('published',0);
		$this->Save();
		$pid=$this->Get('parent');
		$id=$this->Get('id');

		return ($pid);
    }

	/************************************************************
	  Filling TV array ($oldTVs), maintenance function.
	  Differs from $modx->getTemplateVars
	*************************************************************/
	function fillOldTVValues(){
		global $modx;
		$tvc = $modx->getFullTableName('site_tmplvar_contentvalues');
		$tvs = $modx->getFullTableName('site_tmplvars');
		$sql = 'SELECT tvs.name as name, tvc.value as value '.
		       "FROM $tvc tvc INNER JOIN $tvs tvs ".
			   'ON tvs.id=tvc.tmplvarid WHERE tvc.contentid ='.$this->fields['id'];
		$result = $modx->db->query($sql);
		$TVs = array();
		while ($row = mysql_fetch_assoc($result))$TVs[$row['name']] = $row['value'];
		return $TVs;
	}

	/************************************************************
	  Fillin TV names array ($tvNames)), maintenance function.
	*************************************************************/
	function fillTVNames(){
		global $modx;
		$this->tvNames = array();
		$result = $modx->db->select('id, name', $modx->getFullTableName('site_tmplvars'));
		while ($row = mysql_fetch_assoc($result)) $this->tvNames[$row['name']] = $row['id'];
	}

	function flushcache(){
		global $modx;
		include_once $modx->config['base_path'].'manager/processors/cache_sync.class.processor.php';
    $sync = new synccache();
    $sync->setCachepath($modx->config['base_path'].'assets/cache/');
    $sync->setReport(false);
    $sync->emptyCache(); // first empty the cache
//$modx->clearCache();
	}

}// end Class

