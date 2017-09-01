<?php
/**
* Hitsteps core plugin
*
* @author    Hitsteps.com <sales@hitsteps.com>
* @copyright 2010-2016 Hitsteps.com
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class hitsteps extends Module
{
	protected $js_state = 0;
	protected $eligible = 0;
	protected $filterable = 1;
	protected static $products = array();
	protected $_debug = 0;

	public function __construct()
	{
		$this->name = 'hitsteps';
		$this->tab = 'administration';
		$this->version = '1.0.0';
		$this->author = 'Hitsteps.com';
		$this->bootstrap = true;
		$this->ps_versions_compliancy = array('min' => '1.4', 'max' => _PS_VERSION_);
		$this->module_key = 'fde3b8ef637db2947b6465bd863e2575';

		parent::__construct();

		$this->displayName = $this->l('Hitsteps Ultimate Web Analytics');
		$this->description = $this->l('Hitsteps Analytics is a powerful real time website visitor manager, it allow you to view and interact with your visitors in real time.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall Hitsteps?');
		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		}
	}

	public function install()
	{
	
		//Configuration::updateValue('HS_API_CODE', '');
		Configuration::updateValue('HS_FLOATCHAT', 1);
		Configuration::updateValue('HS_LANG', 'auto');


		
		parent::install();

		$this->registerHook('header') ;
		$this->registerHook('dashboardZoneTwo');
		$this->registerHook('dashboardZoneOne');

			
		return true;
	}

	public function uninstall()
	{
	
			$this->unregisterHook('header') ;
			$this->unregisterHook('dashboardZoneTwo');
			$this->unregisterHook('dashboardZoneOne');
				
	
		if (!parent::uninstall())
		{
			return false;
		}
	}




	public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;		// false -> remove toolbar
		$helper->toolbar_scroll = true;	  // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		$fields_form = array();
		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Hitsteps API Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Hitsteps API Code'),
					'name' => 'HS_API_CODE',
					'size' => 20,
					'required' => true,
					'hint' => $this->l('Find your API code once you add your website in your hitsteps account in setting page.')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Enable Floating Chat'),
					'name' => 'HS_FLOATCHAT',
					'hint' => $this->l('By enabling this you show floating chat widget on bottom right of pages'),
					'values'    => array(
						array(
							'id' => 'HS_floatchat_enabled',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'HS_floatchat_disabled',
							'value' => 0,
							'label' => $this->l('Disabled')
						),
					),
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Live Chat default language'),
					'name' => 'HS_LANG',
					'hint' => $this->l('You can control which language displayed to users, or let it default so we detect user langauge'),
					'values'    => array(
						array(
							'id' => 'HS_lang_auto',
							'value' => 'auto',
							'label' => $this->l('Auto')
						),
						array(
							'id' => 'HS_lang_en',
							'value' => 'en',
							'label' => $this->l('English')
						),
						array(
							'id' => 'HS_lang_es',
							'value' => 'es',
							'label' => $this->l('Español')
						),
						array(
							'id' => 'HS_lang_fr',
							'value' => 'fr',
							'label' => $this->l('Français')
						),
						array(
							'id' => 'HS_lang_de',
							'value' => 'de',
							'label' => $this->l('Deutsch')
						),
						array(
							'id' => 'HS_lang_ru',
							'value' => 'ru',
							'label' => $this->l('Русский')
						),
						array(
							'id' => 'HS_lang_fa',
							'value' => 'fa',
							'label' => $this->l('فارسی')
						),
						array(
							'id' => 'HS_lang_tr',
							'value' => 'tr',
							'label' => $this->l('Türkçe')
						)
					),
				),				
			),
			'submit' => array(
				'title' => $this->l('Save'),
			)
		);

		// Load current value
		$helper->fields_value['HS_API_CODE'] = Configuration::get('HS_API_CODE');
		$helper->fields_value['HS_FLOATCHAT'] = Configuration::get('HS_FLOATCHAT');
		$helper->fields_value['HS_LANG'] = Configuration::get('HS_LANG');

		return $helper->generateForm($fields_form);
	}

	/**
	 * back office module configuration page content
	 */
	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submit'.$this->name))
		{
			$HS_API_CODE = Tools::getValue('HS_API_CODE');
			//if (!empty($HS_API_CODE))
			{
				Configuration::updateValue('HS_API_CODE', $HS_API_CODE);
				Configuration::updateValue('hitsteps_CONFIGURATION_OK', true);
				$output .= $this->displayConfirmation($this->l('Hitsteps API code updated successfully'));
			}
			$HS_floatchat_enabled = Tools::getValue('HS_FLOATCHAT');
			if (null !== $HS_floatchat_enabled)
			{
				Configuration::updateValue('HS_FLOATCHAT', (bool)$HS_floatchat_enabled);
				$output .= $this->displayConfirmation($this->l('Hitsteps Float Chat setting updated successfully'));
			}
			$HS_lang = Tools::getValue('HS_LANG');
			if (null !== $HS_lang)
			{
				Configuration::updateValue('HS_LANG', $HS_lang);

			}
		}

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$output .= $this->displayForm();
		}
		else
		{
			$this->context->smarty->assign(array(
				'account_id' => Configuration::get('HS_API_CODE'),
			));
			$output .= $this->display(__FILE__, 'views/templates/admin/form-ps14.tpl');
		}

		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl').$output;
	}

	protected function _getHitstepsTag($back_office = false)
	{
	if ($back_office){return '';}
	
		$uid = '';
		$uname = '';
		if ($this->context->customer && $this->context->customer->isLogged()
		){
			$uid = (int)$this->context->customer->id;
			$uname = (int)$this->context->customer->lastname;
			//$uemail = (int)$this->context->customer->email;
		}


		
		return '
		<!-- HITSTEPS TRACKING CODE<?php echo $htssl; ?> v1.00 PS - DO NOT CHANGE -->
			<script type="text/javascript"> '.(($uid) ? '_hs_uniqueid=\''.$uid.'\';': '')
				.(($uname) ? 'ipname=\''.$uname.'\';': '').'
				(function(){
				var hstc=document.createElement(\'script\');
				hstc.src=\'//www.hitsteps.com/track.php?code='.Tools::substr(Tools::safeOutput(Configuration::get('HS_API_CODE')),0,32).(Tools::safeOutput(Configuration::get('HS_LANG'))? '&lang='.Tools::safeOutput(Configuration::get('HS_LANG')): '').'\';
				hstc.async=true;
				var htssc = document.getElementsByTagName(\'script\')[0];
				htssc.parentNode.insertBefore(hstc, htssc);
				})(); 
			</script>'.
				(Configuration::get('HS_FLOATCHAT')? '
				<script src="//www.hitsteps.com/onlinefloat.php?code='.Tools::substr(Tools::safeOutput(Configuration::get('HS_API_CODE')),0,32).(Tools::safeOutput(Configuration::get('HS_LANG'))? '&lang='.Tools::safeOutput(Configuration::get('HS_LANG')): '').'" type="text/javascript" ></script>':'').				
				'
		<!-- HITSTEPS TRACKING CODE -- DO NOT CHANGE -->
			';
	}

	public function hookHeader()
	{
		if (Configuration::get('HS_API_CODE'))
		{
			return $this->_getHitstepsTag();
		}
	}

	public function hookDashboardZoneTwo($params)
	{
	
	if (Configuration::get('HS_API_CODE')!=''){
		$this->context->smarty->assign(
			array(
				'apicode' => Configuration::get('HS_API_CODE'),
			)
		);
	


		return $this->display(__FILE__, 'dashboard_zone_two.tpl');
	}
	}
	public function hookDashboardZoneOne($params)
	{
	if (Configuration::get('HS_API_CODE')!=''){
		$this->context->smarty->assign(
			array(
				'apicode' => Configuration::get('HS_API_CODE'),
			)
		);
	
		


		return $this->display(__FILE__, 'dashboard_zone_one.tpl');
	}}
	


	protected function _debugLog($function, $log)
	{
		if (!$this->_debug){
		return true;
		}

		$myFile = _PS_MODULE_DIR_.$this->name.'/logs/hitsteps.log';
		$fh = fopen($myFile, 'a');
		fwrite($fh, date('F j, Y, g:i a').' '.$function."\n");
		fwrite($fh, print_r($log, true)."\n\n");
		fclose($fh);
	}
}
