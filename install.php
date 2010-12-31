<?php
/**
  * Install or update CMS_i18n module
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../cms_rc_admin.php');

//check if module is already installed (if so, it is an update)
$sql = "select * from modules where codename_mod='cms_i18n'";
$q = new CMS_query($sql);
$installed = false;
if ($q->getNumRows()) {
	$installed = true;
}

if (!$installed) {
	echo "CMS_i18n installation : Not installed : Launch installation ...<br />";
	if (CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_i18n.sql',true)) {
		CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_i18n.sql',false);
		echo "CMS_i18n installation : Installation done.<br /><br />";
	} else {
		echo "CMS_i18n installation : INSTALLATION ERROR ! Problem in SQL syntax (SQL tables file) ...<br />";
	}
} else {
	echo "CMS_i18n installation : Already installed : update done.<br />";
}
?>