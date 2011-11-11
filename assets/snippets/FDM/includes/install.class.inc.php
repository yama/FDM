<?php
/*
 * Class installation 
 * Class for MODx installationation
 * Version: 0.0.0
 * Author: David "Soda" (soda@dp-site.fr)
 * Date: Juin 06, 2007 09:43 CET
 */

/* 
0.0.0 first try
*/

class installation {
	var $path;
	var $files = array();
	var $snippets = array();
	var $chunks=array();
	var $docs=array();
	var $templates = array();
	var $tvs = array();	
	var $ctime;
	
	
	function installation() {
		global $modx;
		$this->path= strtr(realpath(dirname(__FILE__)), '\\', '/');
		$this->path= str_replace('includes', 'install/',$this->path);
		$this->ctime = time();
	}
	function GetP($field) {
		return $this->parameters[$field];
	}
	
	function SetP($field, $value) {
		$this->parameters[$field] = $value;
	}	

	function Run() {
	//	$this->RunSnippet();
	//	$this->RunChunk();
		$this->RunSql('snippet');
		$this->RunSql('chunk');
		if (!$this->rmdirRecursive($this->path)) {
			echo'<p class="error"> Can\'t erase '.$this->path.' This directory is no more needed</p>';
		}else{
			echo'<p>'.$this->path.' deleted</p>';
		}
	}	
	
function RunSql($type) {
		global $modx;
		//echo "<pre>";echo print_r($this)."</pre>";
		switch ($type) {
			case 'snippet': 
				$myarray=$this->snippets;
				$table='site_snippets';
				break;
			case 'chunk': 
				$myarray=$this->chunks;
				$table='site_htmlsnippets';
				break;
			default:
				echo'<p class="error">type introuvable doit etre snippet ou chunk</p>';	
		}
		if ($myarray){
			echo "<h3>Installation des ".ucfirst($type)."s.</h3>";
			$tbl=$modx->getFullTableName($table);
			foreach($myarray as $name =>$values){
//		echo print_r($value);	
//echo $snipname.'descr:'.$values['description'];exit;				
				$description=($values['description'])?$modx->db->escape($values['description']):' ';
				$filename=$values['filename'];
				$fullname=$this->path.$filename;
				if (is_file($fullname)){
					echo "<h3>Install  '$name', file: $filename</h3>";
					$content=file_get_contents ($fullname);
					$a='<?php';$b='?>';
					$content=str_replace( array($a,$b) , '' , $content);
					$content=$modx->db->escape($content);
					$cnt = $modx->db->getValue("SELECT COUNT(*) FROM  $tbl WHERE name = '$name'");
					if ($cnt ==0){
						echo "<p>Inserting</p>";
						$sql="INSERT INTO $tbl (name,snippet,description) VALUES ('$name','$content','$description')";				
					}else{
						echo "<p>Updating</p>";
						$sql="UPDATE $tbl SET snippet='$content', description='$description' WHERE name = '$name' LIMIT 1";
					}
					$result=$modx->db->query($sql);
					if (!$result)echo '<p class="error">MySQL error.</p>';
				}else{
					echo '<p class="error">'.$fullname.' not found.</p>';
				}
			}				
		}
	}
	
	// rmdirRecursive - detects symbollic links on unix 
	function rmdirRecursive($path,$followLinks=false) {   
		$dir = opendir($path) ;
		while ($entry = readdir($dir)) {       
			if (is_file("$path/$entry") || ((!$followLinks) && is_link("$path/$entry"))) {
				@unlink( "$path/$entry" );
	   	}
	   	elseif (is_dir("$path/$entry") && $entry!='.' && $entry!='..') {
				rmdirRecursive("$path/$entry"); // recursive
			}
		}
		closedir($dir);
		return @rmdir($path);
	}

}
?>