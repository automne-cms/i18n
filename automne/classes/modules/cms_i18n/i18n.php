<?php
// +----------------------------------------------------------------------+
// | Automne (TM)														  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2000-2010 WS Interactive								  |
// +----------------------------------------------------------------------+
// | Automne is subject to version 2.0 or above of the GPL license.		  |
// | The license text is bundled with this package in the file			  |
// | LICENSE-GPL, and is available through the world-wide-web at		  |
// | http://www.gnu.org/copyleft/gpl.html.								  |
// +----------------------------------------------------------------------+
// | Author: Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>      |
// +----------------------------------------------------------------------+

/**
  * Class CMS_i18n
  *
  * Add useful methods for internationalization
  *
  * @package Automne
  * @subpackage cms_i18n
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

class CMS_i18n extends CMS_grandFather
{
	/**
	  * Class vars
	  */
	private $_page = null;
	private $_pageId = null;
	private $_language = null;
	/**
	  * Create and get class instance
	  * This is a singleton : the class is always the same anywhere
	  *
	  * @return void
	  * @access public
	  * @static
	  */
	private static $_instance = false;
	private function __constructor() {}
	static public function getInstance() {
		if (!CMS_i18n::$_instance) {
			CMS_i18n::$_instance = new CMS_i18n();
		}
		return CMS_i18n::$_instance;
	}
	function setContext($pageID = '', $languageCode = '') {
		if (io::isPositiveInteger($pageID)) {
			$this->_pageId = $pageID;
			return true;
		}
		if ($languageCode) {
			$this->_language = $languageCode;
		}
		return false;
	}
	function getPage() {
		if (!is_object($this->_page) && io::isPositiveInteger($this->_pageId)) {
			$this->_page = CMS_tree::getPageByID($this->_pageId);
		} elseif(!is_object($this->_page)) {
			return false;
		}
		return $this->_page;
	}
	
	/**
	  * Return a translated string for a given key
	  * 
	  * @param string $key the string key to get translation
	  * @param string $language The language code to get translation to
	  * @param string $parameters The parameters to replace in translation. Each parameter must be separated by ::
	  * @return string : the stanslated string
	  * @access public
	  */
	function getTranslation($key, $language = '', $parameters = '') {
		static $messages;
		global $cms_language;
		//get current language if none given
		if (!$language) {
			if ($this->_language) {
				$language = $this->_language;
			} else {
				//check page instance
				if (!is_object($this->_page) && io::isPositiveInteger($this->_pageId)) {
					$this->_page = CMS_tree::getPageByID($this->_pageId);
				} elseif(!is_object($this->_page) && !is_object($cms_language)) {
					return false;
				}
				//get anguage from current page
				if (is_object($this->_page) && !$this->_page->hasError()) {
					$language = $this->_page->getLanguage(true);
				}
				//get language from cms_language if exists
				if ((!is_object($this->_page) || $this->_page->hasError()) && is_object($cms_language)) {
					$language = $cms_language->getCode();
				}
				//set current language
				if ($language) {
					$this->_language = $language;
				}
			}
		}
		if (!$language) {
			return false;
		}
		if (!isset($messages[$key][$language])) {
			$q = new CMS_query("
				SELECT 
					msgref.message_mes as msg
				FROM 
					messages as keyref , messages as msgref 
				WHERE 
					keyref.module_mes = 'cms_i18n_vars'
					and msgref.module_mes = 'cms_i18n_vars'
					and keyref.module_mes = msgref.module_mes 
					and keyref.message_mes = '".io::sanitizeSQLString($key)."' 
					and keyref.id_mes = msgref.id_mes 
					and msgref.language_mes = '".io::sanitizeSQLString($language)."'
			");
			if ($q->getNumRows() == 1) {
				$messages[$key][$language] = $q->getValue('msg');
			} else {
				$messages[$key][$language] = false;
			}
		}
		if ($messages[$key][$language] === false) {
			return 'Unknown key: '.$key;
		}
		if ($parameters) {
			$parameters = explode('::', $parameters);
			$replacement = SensitiveIO::arraySprintf($messages[$key][$language], $parameters);
			if (!$replacement) {
				return $messages[$key][$language];
			} else {
				return $replacement;
			}
		}
		return $messages[$key][$language];
	}
}