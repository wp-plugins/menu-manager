<?php
/**
 * This file is part of Menu Manager. Please see the menu-manager.php file for copyright
 *
 * @author Sulaeman
 * @version 1.0.4
 * @package menu-manager
 */

function has_sub_menu($menu_structure, $parent = 0)
{
	if (is_array($menu_structure) && isset($menu_structure[$parent]) && count($menu_structure[$parent]) > 0)
	{
		return TRUE;
	}
	
	return FALSE;
}

function create_menu_dragdrop_item($pages, $categories, $selected_pages_full, $selected_categories_full, $menu_structure, $parent = 0)
{
	if (is_array($menu_structure) && isset($menu_structure[$parent]) && count($menu_structure[$parent]) > 0)
	{
		foreach($menu_structure[$parent] as $menu)
		{
			$menu->menu_parent = ($menu->menu_parent != 0) ? $menu->menu_parent : '';
			if ($menu->menu_type == 'page')
			{
				foreach($pages as $page)
				{
					if ($page->ID == $selected_pages_full[$menu->menu_value]['value'])
					{
						$parent_page = get_parent_page_name($pages, $page->post_parent);
						$menu->menu_name = (($parent_page != '') ? $parent_page.' &raquo; ' : '') . $page->post_title;
						break;
					}
				}
			}
			elseif ($menu->menu_type == 'category')
			{
				foreach($categories as $ct)
				{
					if ($ct->cat_ID == $selected_categories_full[$menu->menu_value]['value'])
					{
						$menu->menu_name = $ct->cat_name;
						break;
					}
				}
			}
?>
		<li class="ui-widget-content ui-corner-tr<?php echo ($parent == 0) ? ((has_sub_menu($menu_structure, $menu->menu_id)) ? '' : ' enable-drag') : ''; ?>">
			<div class="head-container">
				<h5 class="ui-widget-header"><?php echo $menu->menu_name; ?>&nbsp;(<?php echo $menu->menu_type; ?>)</h5>
			</div>
			<input class="sys_menu_unid" type="hidden" name="main_menu[unid][]" value="<?php echo $menu->menu_id; ?>" style="width:30px;" />
			<input class="sys_menu_parent" type="hidden" name="main_menu[parent][]" value="<?php echo $menu->menu_parent; ?>" style="width:30px;" />
			Position: <input type="text" name="main_menu[sort][]" value="<?php echo $menu->menu_order; ?>" style="width:30px;" />
			<div style="clear:both; margin:0 0 0.5em 0;"></div>
			<div class="dropbox">drop to this box</div>
			<div class="dropbox_pony" style="display:none;"></div>
			<div style="clear:both;"></div>
			<div class="droppedbox">
				<ul class="gallery ui-helper-reset">
					<?php create_menu_dragdrop_item($pages, $categories, $selected_pages_full, $selected_categories_full, $menu_structure, $menu->menu_id); ?>
					<div style="clear:both;"></div>
				</ul>
			</div>
			<div style="clear:both;"></div>
			<a href="#" title="Move out this" class="ui-icon ui-icon-refresh" style="display:<?php echo ($menu->menu_parent > 0) ? 'block' : 'none'; ?>">Move Out</a>
		</li>
<?php
		}
	}
}

function get_parent_page_name($pages, $parent)
{
	foreach($pages as $page)
	{
		if ($page->ID == $parent)
		{
			return $page->post_title;
		}
	}
}
?>

<link type="text/css" media="screen" rel="stylesheet" href="<?php echo MM_DISPLAY_URL; ?>/styles/jquery/jquery-ui.css" />
<style>
#mm_gallery { float: left; width: 100%; min-height: 12em; } * html #mm_gallery { height: 12em; } /* IE6 */
.mm_gallery.custom-state-active { background: #eee; }
.mm_gallery li { float: left; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; border:1px solid #999999;}
.head-container h5 { padding:  0.5em; }
.mm_gallery li h5 { margin: 0 0 0.4em; padding:  0.5em; cursor: move; }
.mm_gallery li a { float: right; }
.mm_gallery li a.ui-icon-zoomin { float: left; }
.mm_gallery li img { width: 100%; cursor: move; }
.mm_gallery li div.dropbox,
.mm_gallery li div.dropbox_pony { width: 100%; height: 40px; border: 1px solid #ff0000; color: #999999;}
.mm_gallery li div.droppedbox { margin: 1em 0 0 0; }

.mm_highlight {border: 1px solid #ffffff; background: #cccccc; color: #444444; }
.mm_highlight h5 { background:#ffffff; color:#000000; }
</style>
<script type="text/javascript" src="<?php echo MM_DISPLAY_URL; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo MM_DISPLAY_URL; ?>/js/superfish.js"></script>
<script type="text/javascript" src="<?php echo MM_DISPLAY_URL; ?>/js/menu-manager.js"></script>

<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>Menu Manager Options</h2>
	<?php if (strlen($message)) : ?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
	<?php unset($message); ?>
	<?php endif; ?>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="post-body">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div class="postbox ">
					<div class="handlediv" title="Click to toggle"><br/></div>
					<h3 class="hndle">
						<span>Main Menu Structure</span>
					</h3>
					<div class="inside">
						<form name="menu_structure" action="" method="post">
							<div id="mm_gallery-frame">
								<ul id="mm_gallery" class="mm_gallery ui-helper-reset ui-helper-clearfix">
									<?php create_menu_dragdrop_item($pages, $post_categories, $selected_pages_full, $selected_categories_full, $menu_structure); ?>
								</ul>
								<div style="clear:both;"></div>
							</div>
							<div id="major-publishing-actions">
								<div id="delete-action"> </div>
								<div id="publishing-action">
									<input class="button-primary" type="submit" value="Save" name="mm_structure_save_options" />
								</div>
								<div class="clear"></div>
							</div>
						</form>
					</div>
				</div><!-- postbox -->
				<div class="postbox" style="float:left;width:400px;">
					<div class="handlediv" title="Click to toggle"><br/></div>
					<h3 class="hndle">
						<span>Pages</span>
					</h3>
					<div class="inside">
						<form name="menu" action="" method="post">
							<table id="newmeta">
								<tr>
									<td class="submit" style="float:none;">
										<?php foreach($pages as $page) : ?>
										<?php $parent = get_parent_page_name($pages, $page->post_parent); ?>
										<div class="item">
											<input id="page_<?= $page->ID; ?>" type="checkbox" class="checkbox" name="page_main_menu[]" value="<?php echo $page->ID; ?>"<?php if (in_array($page->ID, $selected_pages)) { echo ' checked="checked"'; } ?>/>
											&nbsp;<label for="page_<?= $page->ID; ?>"><?php echo (($parent != '') ? '<strong>'.$parent.'</strong> &raquo; ' : '').$page->post_title; ?></label>
										</div>
										<?php endforeach; ?>
									</td>
								</tr>
							</table>
							<br/>
							<div id="major-publishing-actions">
								<div id="delete-action"> </div>
								<div id="publishing-action">
									<input class="button-primary" type="submit" value="Save" name="mm_save_options_page" />
								</div>
								<div class="clear"></div>
							</div>
						</form>
					</div>
				</div><!-- postbox -->
				<div class="postbox" style="float:left;width:400px;margin-left:10px;">
					<div class="handlediv" title="Click to toggle"><br/></div>
					<h3 class="hndle">
						<span>Categories</span>
					</h3>
					<div class="inside">
						<form name="menu2" action="" method="post">
							<table id="newmeta">
								<tr>
									<td class="submit" style="float:none;">
										<?php if (is_array($post_categories)) : ?>
										<?php foreach($post_categories as $pc) : ?>
										<div class="item">
											<input id="cat_<?php echo $pc->cat_ID; ?>" type="checkbox" class="checkbox" name="cat_main_menu[]" value="<?php echo $pc->cat_ID; ?>"<?php if (in_array($pc->cat_ID, $selected_categories)) { echo ' checked="checked"'; } ?>/>
											&nbsp;<label for="cat_<?php echo $pc->cat_ID; ?>"><?php echo $pc->cat_name; ?></label>
										</div>
										<?php endforeach; ?>
										<?php endif; ?>
									</td>
								</tr>
							</table>
							<br/>
							<div id="major-publishing-actions">
								<div id="delete-action"> </div>
								<div id="publishing-action">
									<input class="button-primary" type="submit" value="Save" name="mm_save_options_category" />
								</div>
								<div class="clear"></div>
							</div>
						</form>
					</div>
				</div><!-- postbox -->
				<div class="postbox" style="float:left;width:400px;margin-left:10px;">
					<div class="handlediv" title="Click to toggle"><br/></div>
					<h3 class="hndle">
						<span>Custom</span>
					</h3>
					<div class="inside">
						<form name="menu3" action="" method="post">
							<table id="newmeta">
								<tr>
									<td class="submit" style="float:none;">
										<div id="sys_cust_link_container">
											<div class="item sys_cust_link_item_example" style="display:none;">
												<input class="sys_menu_delete" type="hidden" name="cust_main_menu[delete][]" value="" />
												<input type="hidden" name="cust_main_menu[unid][]" value="" />
												<input type="hidden" name="cust_main_menu[parent][]" value="0" />
												Label:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[name][]" value="" style="width:250px;" />
												<br/>
												Url:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[url][]" value="" style="width:250px;" />
												<br/>
												Class:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[class][]" value="" style="width:250px;" />
												<hr/>
											</div>
											<?php if (is_array($selected_custom_menu) && count($selected_custom_menu) > 0) : ?>
											<?php $i_menu = 0; ?>
											<?php foreach($selected_custom_menu as $menu) : ?>
											<div class="item">
												<input class="sys_menu_delete" type="hidden" name="cust_main_menu[delete][]" value="" />
												<input type="hidden" name="cust_main_menu[unid][]" value="<?php echo $menu['unid']; ?>" />
												<input type="hidden" name="cust_main_menu[parent][]" value="<?php echo $menu['parent']; ?>" />
												Label:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[name][]" value="<?php echo $menu['name']; ?>" style="width:250px;" />
												<br/>
												Url:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[url][]" value="<?php echo $menu['url']; ?>" style="width:250px;" />
												<br/>
												Class:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[class][]" value="<?php echo $menu['class']; ?>" style="width:250px;" />
												<p class="submit">
													<input type="button" class="sys_but_delete_custom_menu" value="remove" style="width:100px;" />
												</p>
												<hr/>
											</div>
											<?php ++$i_menu; ?>
											<?php endforeach; ?>
											<?php else: ?>
											<div class="item">
												<input class="sys_menu_delete" type="hidden" name="cust_main_menu[delete][]" value="" />
												<input type="hidden" name="cust_main_menu[unid][]" value="" />
												<input type="hidden" name="cust_main_menu[parent][]" value="0" />
												Label:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[name][]" value="" style="width:250px;" />
												<br/>
												Url:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[url][]" value="" style="width:250px;" />
												<br/>
												Class:&nbsp;<input id="trackback_url" class="code"  type="text" name="cust_main_menu[class][]" value="" style="width:250px;" />
												<hr/>
											</div>
											<?php endif; ?>
										</div>
										<div id="publishing-action">
											<input id="sys_but_add_cust_link" class="button-primary" type="button" value="add another custom" name="addmore" />
										</div>
									</td>
								</tr>
							</table>
							<br/>
							<div id="major-publishing-actions">
								<div id="delete-action"> </div>
								<div id="publishing-action">
									<input class="button-primary" type="submit" value="Save" name="mm_save_options_custom" />
								</div>
								<div class="clear"></div>
							</div>
						</form>
					</div>
				</div><!-- postbox -->
				<div style="clear:both;"></div>
			</div>
		</div>
		<br class="clear"/>
	</div>
</div>