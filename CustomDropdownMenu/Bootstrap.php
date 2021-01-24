<?php
/**
 * Custom DropdownMenu Plugin
 * @package CustomDropDownMenu
 * @subpackage Bootstrap
 * @author Custom <info@bay20.com>
 */
use Doctrine\Common\Collections\ArrayCollection;
use CustomDropdownMenu\Subscriber\Frontend;
class Shopware_Plugins_Frontend_CustomDropdownMenu_Bootstrap extends Shopware_Components_Plugin_Bootstrap {
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install() {
		$this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onFrontStartDispatch');
		$this->subscribeEvent('Shopware_Console_Add_Command', 'onFrontStartDispatch');
		
		$this->subscribeEvent ( 'Theme_Compiler_Collect_Plugin_Less', 'onCollectLessFiles' );
		
		$this->subscribeEvent ( 'Theme_Compiler_Collect_Plugin_Javascript', 'onCollectJavascriptFiles' );
		
		$this->createMenu();
		return [
				'success' => true,
				'invalidateCache' => ['template', 'theme']
		];
	}
	
	/**
	 *
	 * Update Function
	 *
	 * @return boolean
	 *
	 */
 /*	public function update($oldVersion) {
		if (version_compare($oldVersion, '1.2.0', '<=')) {
			throw new Exception('Please reinstall (un- and install) the plugin! Your settings are safe in the Db. Thanks!');
		}
		else{
			return array(
					'success' => true,
					'invalidateCache' => array('frontend', 'theme')
			);
		}
	}*/
	
	/**
	 * Clear and rebuild Template Cache
	 * @return array
	 */
	public function enable()
	{
		return [
				'success' => true,
				'invalidateCache' => ['template', 'theme']
		];
	}
	
	/**
	 * Register the PluginNamespace
	 */
	private function registerPluginNamespaces()
	{
		$this->get('loader')->registerNamespace(
				'CustomDropdownMenu',
				$this->Path()
		);
	}
	
	/**
	 * The afterInit function registers the namespace.
	 */
	public function afterInit() {
		$this->registerPluginNamespaces();
	}
	
	/**
	 * Creates the menu-entry for the backend.
	 */
	public function createMenu(){
		$form = $this->Form ();
		
		$parent = $this->Forms ()->findOneBy ( array (
				'name' => 'Frontend'
		) );
		$form->setParent ( $parent );
		
		$form->setElement ( 'checkbox', 'show', array (
				'label' => 'Show Menu',
				'value' => 1,
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
		) );
		$form->setElement ( 'number', 'levels', array (
				'label' => 'Number of levels',
				'value' => 2,
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
		) );
		$form->setElement ( 'boolean', 'caching', array (
				'label' => 'Enable caching',
				'value' => 1
		) );
		$form->setElement ( 'number', 'cachetime', array (
				'label' => 'Cache time',
				'value' => 86400
		) );
		
		$form->setElement ( 'boolean', 'showHome', array (
				'label' => 'Home in Men & uuml; Show',
				'value' => 1
		) );
		
		$form->setElement ( 'boolean', 'customSticky', array (
				'label' => 'Fixed display of menu at top when scrolling (sticky)',
				'value' => 0
		) );
		
		$form->setElement('text', 'customBgColorHoverTop',
				array(
						'label' => 'hover background top',
						'value' => '@brand-primary',
						'description' => 'hover background color of the 1st level - default color from theme (Input ex.:#ff1111)',
						'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
				)
		);
		
		$form->setElement('text', 'customBgColorSub',
				array(
						'label' => 'Background sub',
						'value' => '@brand-primary',
						'description' => 'Background color of the submenu items - default color from theme (Input ex.:#ff1111)',
						'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
				)
		);
		
		$form->setElement('text', 'customBgColorHoverSub',
				array(
						'label' => 'hover background sub',
						'value' => '@brand-primary-light',
						'description' => 'Background color of the submenu items - default color from theme (Input ex.:#ff1111)',
						'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
				)
		);
		
		$form->setElement('text', 'customBorderColor',
				array(
						'label' => 'Frame color',
						'value' => '@border-color',
						'description' => 'Frame color - default color from theme (Input ex.:#ff1111)',
						'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
				)
		);
		$form->setElement ( 'number', 'customSubmenuWidth', array (
				'label' => 'Submenu & uuml; -width',
				'value' => 250,
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
		) );
		
		$form->setElement ( 'number', 'customSubmenuHeight', array (
				'label' => 'Height of a submenu entry',
				'value' => 33,
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
		) );
		
		$form->setElement ( 'text', 'customSubmenuFontSize', array (
				'label' => 'Submenu font size',
				'value' => 0.875,
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
				'description' => 'Font size of the submenu items (Input ex.:0.8)',
		) );
	}
	
	/**
	 *
	 * @return ArrayCollection
	 */
	public function onCollectLessFiles() {
		$lessDir = __DIR__ . '/Views/frontend/_public/src/less/';
		
		$less = new \Shopware\Components\Theme\LessDefinition ( array (
				'custom-bg-color-hover-top' => $this->Config()->get('customBgColorHoverTop'),
				'custom-bg-color-sub' => $this->Config()->get('customBgColorSub'),
				'custom-bg-color-hover-sub' => $this->Config()->get('customBgColorHoverSub'),
				'custom-border-color' => $this->Config()->get('customBorderColor'),
				'custom-submenu-width' => $this->Config()->get('customSubmenuWidth'),
				'custom-submenu-height' => $this->Config()->get('customSubmenuHeight'),
				'custom-submenu-font-size' => $this->Config()->get('customSubmenuFontSize')
		), array (
				$lessDir . 'dropdown-menu.less' 
		) );
		
		return new ArrayCollection ( array (
				$less 
		) );
	}
	
	/**
	 *
	 * @return ArrayCollection
	 */
	public function onCollectJavascriptFiles() {
		$javascriptDir = __DIR__ . '/Views/frontend/_public/src/js/';
	
		return new ArrayCollection ( array (
				$javascriptDir . 'doubletaptogo.js',
				$javascriptDir . 'jquery.menu-scroller.js',
				$javascriptDir . 'jquery.dropdownMenu.js'
		) );
	}
	
	/**
	 * The startDispatch handler
	 */
	public function onFrontStartDispatch()
	{
		$this->registerPluginNamespaces();
	
		$subscribers = [
				new Frontend($this),
		];
	
		foreach ($subscribers as $subscriber) {
			$this->get('events')->addSubscriber($subscriber);
		}
	}
	
	/**
	 * Get label of this plugin to display in manager
	 *
	 * @return array
	 */
	public function getLabel() {
		return 'Custom Dropdown Menu';
	}

	/**
	 * Get information of this plugin to display in manager
	 *
	 * @return array
	 */
	public function getInfo() {
		return array (
				'version' => $this->getVersion (),
				'label' => $this->getLabel (),
				'author' => 'Bay 20',
				'license' => 'proprietary',
				'description' => 'Custom Dropdown Menu developed by Bay20',
				'support' => 'info@bay20.com',
				'link' => 'http://www.bay20.com'
		);
	}
}
