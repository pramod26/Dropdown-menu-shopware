<?php
namespace CustomDropdownMenu\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;

class Frontend implements SubscriberInterface
{
	/**
	 * @var string
	 */
	private $bootstrap;

	/**
	 * @param string $bootstrapPath
	 */
	public function __construct($bootstrap)
	{
		$this->bootstrap = $bootstrap;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchFrontend'
		);
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Controller_ActionEventArgs $args
	 */
	public function onPostDispatchFrontend(\Enlight_Controller_ActionEventArgs $args) {
		$config = Shopware()->Plugins()->Frontend()->CustomDropdownMenu()->Config ();
	
		if (! $config->show) {
			return;
		}
	
		$view = $args->getSubject ()->View ();
		$parent = Shopware ()->Shop ()->get ( 'parentID' );
		$categoryId = $args->getRequest ()->getParam ( 'sCategory', $parent );
	
		$menu = $this->getDropdownMenu ( $parent, $categoryId, ( int ) $config->levels );
		$view->assign ( 'sDropdownMenu', $menu );
		$view->assign ( 'customDropDownConfig', $config );
		$view->addTemplateDir ( $this->bootstrap->Path () . 'Views' );
		$view->extendsTemplate ( 'frontend/plugins/dropdown_menu/index.tpl' );
	}
	
	/**
	 * Returns the complete menu with category path.
	 *
	 * @param int $category
	 * @param int $activeCategoryId
	 * @param int $depth
	 * @return array
	 */
	public function getDropdownMenu($category, $activeCategoryId, $depth = null) {
		$context = Shopware ()->Container ()->get ( 'shopware_storefront.context_service' )->getShopContext ();
		$cacheKey = 'Shopware_DropdownMenu_Tree_' . $context->getShop ()->getId () . '_' . $category . '_' . Shopware ()->System ()->sUSERGROUPDATA ['id'];
		$cache = Shopware ()->Container ()->get ( 'cache' );
		$config = Shopware()->Plugins()->Frontend()->CustomDropdownMenu()->Config();
		if ($config->caching && $cache->test ( $cacheKey )) {
			$menu = $cache->load ( $cacheKey );
		} else {
			$ids = $this->getCategoryIdsOfDepth ( $category, $depth );
			$categories = Shopware ()->Container ()->get ( 'shopware_storefront.category_service' )->getList ( $ids, $context );
			$categoriesArray = $this->convertCategories ( $categories );
			$categoryTree = $this->getCategoriesOfParent ( $category, $categoriesArray );
			if ($config->caching) {
				$cache->save ( $categoryTree, $cacheKey, [
						'Shopware_Plugin'
				], ( int ) $config->cachetime );
			}
			$menu = $categoryTree;
		}
	
		$categoryPath = $this->getCategoryPath ( $activeCategoryId );
		$menu = $this->setActiveFlags ( $menu, $categoryPath );
		return $menu;
	}
	
	/**
	 * Set active categorie of menu
	 * @param array[] $categories
	 * @param int[] $actives
	 * @return array[]
	 */
	public function setActiveFlags($categories, $actives) {
		foreach ( $categories as &$category ) {
			$category ['flag'] = in_array ( $category ['id'], $actives );
				
			if (! empty ( $category ['sub'] )) {
				$category ['sub'] = $this->setActiveFlags ( $category ['sub'], $actives );
			}
		}
		return $categories;
	}
	
	/**
	 * Get all categories of the menu
	 * @param int $categoryId
	 * @return int[]
	 * @throws Exception
	 */
	public function getCategoryPath($categoryId) {
		$query = Shopware ()->Container ()->get ( 'dbal_connection' )->createQueryBuilder ();
	
		$query->select ( 'category.path' )->from ( 's_categories', 'category' )->where ( 'category.id = :id' )->setParameter ( ':id', $categoryId );
	
		$path = $query->execute ()->fetch ( \PDO::FETCH_COLUMN );
		$path = explode ( '|', $path );
		$path = array_filter ( $path );
		$path [] = $categoryId;
	
		return $path;
	}
	
	/**
	 * Get all submenu points of one category
	 * @param int $parentId
	 * @param int $depth
	 * @return int[]
	 * @throws Exception
	 */
	public function getCategoryIdsOfDepth($parentId, $depth) {
		$query = Shopware ()->Container ()->get ( 'dbal_connection' )->createQueryBuilder ();
		$query->select ( "DISTINCT category.id" )->from ( 's_categories', 'category' )->where ( 'category.path LIKE :path' )->andWhere ( 'category.active = 1' )->andWhere ( 'ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth' )->orderBy ( 'category.position' )->setParameter ( ':depth', $depth )->setParameter ( ':path', '%|' . $parentId . '|%' );
	
		/**
		 * @var $statement PDOStatement
		*/
		$statement = $query->execute ();
		return $statement->fetchAll ( \PDO::FETCH_COLUMN );
	}
	
	/**
	 * Get all categories of an parent category
	 * @param int $parentId
	 * @param array $categories
	 * @return array
	 */
	public function getCategoriesOfParent($parentId, $categories) {
		$result = [ ];
	
		foreach ( $categories as $index => $category ) {
			if ($category ['parentId'] != $parentId) {
				continue;
			}
			$children = $this->getCategoriesOfParent ( $category ['id'], $categories );
			$category ['sub'] = $children;
			$category ['activeCategories'] = count ( $children );
			$result [] = $category;
		}
	
		return $result;
	}
	
	/**
	 * Get data for all categories
	 * @param Category[] $categories
	 * @return array
	 */
	public function convertCategories($categories) {
		$converter = Shopware ()->Container ()->get ( 'legacy_struct_converter' );
		return array_map ( function (Category $category) use($converter) {
			$data = [
					'id' => $category->getId (),
					'name' => $category->getName (),
					'parentId' => $category->getParentId (),
					'hidetop' => ! $category->displayInNavigation (),
					'active' => 1,
					'cmsHeadline' => $category->getCmsHeadline (),
					'cmsText' => $category->getCmsText (),
					'position' => $category->getPosition (),
					'link' => 'shopware.php?sViewport=cat&sCategory=' . $category->getId (),
					'media' => null,
					'flag' => false
			];
				
			if ($category->isBlog ()) {
				$data ['link'] = 'shopware.php?sViewport=blog&sCategory=' . $category->getId ();
			}
				
			if ($category->getMedia ()) {
				$data ['media'] = $converter->convertMediaStruct ( $category->getMedia () );
				$data ['media'] ['path'] = $category->getMedia ()->getFile ();
			}
				
			$externalLink = $category->getExternalLink();
			if (!empty($externalLink)) {
				$data['link'] = $category->getExternalLink();
				$data['external'] = $category->getExternalLink();
				$data['externalTarget'] = $category->getExternalTarget();
			}
				
			return $data;
		}, $categories );
	}
	
	/**
	 * Set the active flag of one category
	 * @param array $categories
	 * @param array $path
	 * @return array
	 */
	public function setActiveCategoriesFlag($categories, $path) {
		foreach ( $path as $categoryId ) {
			$categories [$categoryId] ['flag'] = true;
		}
	
		return $categories;
	}
}