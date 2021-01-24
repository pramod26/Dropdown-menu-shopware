{extends file="parent:frontend/index/index.tpl"}

	{block name='frontend_index_before_page' prepend}
		<div style="display: none;" data-dropdownMenu="true"></div>
	{/block}

	{block name='frontend_index_navigation_categories_top'}
			{if $theme.checkoutHeader && ( ({controllerName} == "checkout" && {controllerAction} != "cart") ||  ({controllerName}=="register") && !$smarty.server.REQUEST_URI|strstr:"account")  }
				 {$smarty.block.parent}
			{else}
					<nav class="navigation-main">
	                    <div class="container" data-menu-scroller="true" data-listSelector=".navigation--list.container" data-viewPortSelector=".navigation--list-wrapper" {if $customDropDownConfig.customSticky}data-sticky="true"{/if}>
					    {function name="categories_top" level=1}
					        <ul class="menu--list menu--level-{$level}">
					            {block name="frontend_plugins_dropdown_menu_list"}
					                {foreach $categories as $category}
					                    {if $category.hidetop}
					                        {continue}
					                    {/if}
					
					                    <li class="menu--list-item item--level-{$level}"{if $level === 0} style="width: 100%"{/if}>
					                        {block name="frontend_plugins_dropdown_menu_list_item"}
					                            <a href="{$category.link}" class="menu--list-item-link" title="{$category.name}" {if $category.external} target="{$category.externalTarget}"{/if}>{$category.name}</a>
					
					                            {if $category.sub}
					                                {call name=categories_top categories=$category.sub level=$level+1}
					                            {/if}
					                        {/block}
					                    </li>
					                {/foreach}
					            {/block}
					        </ul>
					    {/function}
					
					<div class="navigation--list-wrapper">
					    <ul class="navigation--list container" role="menubar" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
					        {strip}
					            {block name='frontend_index_navigation_categories_top_home'}
					                {if $customDropDownConfig.showHome}
						                <li class="navigation--entry{if $sCategoryCurrent == $sCategoryStart && $Controller == 'index'} is--active{/if} is--home" role="menuitem">
						                    <a class="navigation--link is--first{if $sCategoryCurrent == $sCategoryStart && $Controller == 'index'} active{/if}" href="{url controller='index'}" title="{s name='IndexLinkHome' namespace="frontend/index/categories_top"}{/s}" itemprop="url">
						                        <span itemprop="name">{s name='IndexLinkHome' namespace="frontend/index/categories_top"}{/s}</span>
						                    </a>
						                </li>
						             {/if}
					            {/block}
					
					            {block name='frontend_index_navigation_categories_top_before'}{/block}
					
					            {foreach $sDropdownMenu as $sCategory}
					                {block name='frontend_index_navigation_categories_top_entry'}
					                    {if !$sCategory.hidetop}
					                         {$hasCategories = $sCategory.activeCategories > 0}
					                         <li class="navigation--entry{if $sCategory.flag} is--active{/if}{if $sCategory.sub} dropactive{/if}" role="menuitem">
					                            <a class="navigation--link{if $sCategory.flag} is--active{/if}" href="{$sCategory.link}" title="{$sCategory.name}" itemprop="url" {if $sCategory.external} target="{$sCategory.externalTarget}"{/if}>
					                                <span itemprop="name">{$sCategory.name}</span>
					                            </a>
					                           {if $hasCategories}
					                                {block name="frontend_plugins_dropdown_menu_sub_categories"}
					                                    {call name="categories_top" categories=$sCategory.sub}
					                                {/block}
					                           {/if}   
					                        </li>
					                    {/if}
					                {/block}
					            {/foreach}
					
					            {block name='frontend_index_navigation_categories_top_after'}{/block}
					        {/strip}
					    </ul>
					</div>
			 </div>
		</nav>
	{/if}
{/block}