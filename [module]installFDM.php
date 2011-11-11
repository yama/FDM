include_once(MODX_BASE_PATH . 'assets/snippets/FDM/includes/install.class.inc.php');

// check your lang: available is FR or EN
$lang = $modx->config['manager_language'];

echo "<h2>Installation FDM.</h2>";
$fdm_install=new installation;
         
$snippet['FDM']['filename']    = '[snippet]FDM.php';
$snippet['FDM']['description'] = 'Front End Doc Manager';

$chunk['fdmEform']['filename']    = $lang . '_eFormTpl_chunk.html';
$chunk['fdmEform']['description'] = 'eForm Template for editing Documents';

$fdm_install->snippets=$snippet;
$fdm_install->chunks=$chunk;
$fdm_install->Run();
