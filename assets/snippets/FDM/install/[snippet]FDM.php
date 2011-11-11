<?php
// documentation is in the archive
include_once($modx->config['base_path'] .'assets/snippets/FDM/includes/fdm.class.inc.php');
$FDM = new CFDM;
$FDM->SetP('language', $language);
$FDM->SetP('debug', $debug);
$FDM->SetP('id',$id);
$FDM->SetP('parent',$parent);
$FDM->SetP('script',$script);
$FDM->SetP('model',$model);
$FDM->SetP('output',$output);
$FDM->SetP('redirect',$redirect);
$FDM->SetP('editorpageid',$editorpageid);
$FDM->SetP('disableadd',$disableadd);
$FDM->SetP('disableedit',$disableedit);
$FDM->SetP('disablepublish',$disablepublish);
$FDM->SetP('disableunpublish',$disableunpublish);
$FDM->SetP('disabledelete',$disabledelete);
$FDM->SetP('moderated', $moderated);
$FDM->SetP('disablemanager',$disablemanager);
$FDM->SetP('guestname', $guestname);
$FDM->SetP('anonymous',$anonymous);
$FDM->SetP('canpost', $canpost);
$FDM->SetP('canview', $canview);
$FDM->SetP('canedit', $canedit);
$FDM->SetP('canmoderate', $canmoderate);
$FDM->SetP('trusted', $trusted);
$FDM->SetP('moderated',$moderated);
$FDM->SetP('notify',$notify);
$FDM->SetP('notify', $notify);
$FDM->SetP('aliastype', $aliastype);
$FDM->SetP('aliasdate', $aliasdate);
$FDM->SetP('realdelete', $realdelete);
$FDM->SetP('tplLinks', $tplLinks);

$FDM->SetP('eform_tpl',$eform_tpl);
$FDM->SetP('eform_formid',$eform_formid);
$FDM->SetP('eform_report',$eform_report);
$FDM->SetP('eform_thankyou',$eform_thankyou);
$FDM->SetP('eform_to',$eform_to);
$FDM->SetP('eform_noemail',$eform_noemail);
$FDM->SetP('eform_subject',$eform_subject);
$FDM->SetP('eform_autosender',$eform_autosender);
$FDM->SetP('eform_debug',$eform_debug);

return $FDM->Run();

?>
