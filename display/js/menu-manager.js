function set_dragable(obj) {
	obj.draggable({
		cancel: 'a.ui-icon',
		revert: 'invalid',
		containment: 'mm_gallery-frame',
		helper: function(ev, ui){
			return jQuery(this).find('div.head-container').clone();
		},
		cursor: 'move',
		handle: 'h5',
		drag: function(ev, ui){
			jQuery(this).find('div.dropbox').hide();
			jQuery(this).find('div.dropbox_pony').show();
		},
		stop: function(ev, ui){
			jQuery(this).find('div.dropbox_pony').hide();
			jQuery(this).find('div.dropbox').show();
		}
	});
}

jQuery(function() {
	jQuery("#mm_nav").superfish();
	
	jQuery('#sys_but_add_cust_link').click(function(){
		jQuery('.sys_cust_link_item_example').clone().removeClass('sys_cust_link_item_example').appendTo(jQuery('#sys_cust_link_container')).css('display', 'block');
	});
	
	jQuery('.sys_but_delete_custom_menu').click(function(){
		var obj = jQuery(this);
		var parent = obj.parent().parent();
		parent.find('.sys_menu_delete').val(1);
		parent.hide();
		delete parent;
		delete obj;
	});
	
	var gallery = jQuery('#mm_gallery');
	if (gallery[0] != null) {
		set_dragable(jQuery('li.enable-drag', gallery));
		
		jQuery('li', gallery).find('div.dropbox').droppable({
			accept: '.mm_gallery > li',
			activeClass: 'mm_highlight',
			drop: function(ev, ui){
				appendItem(ui.draggable, this);
			}
		});
	}

	function appendItem(item, droppable) {
		item.fadeOut(function() {
			var receiver = jQuery(droppable).parent();
			var list = receiver.find('.droppedbox ul:first');
			
			receiver.draggable('destroy');
			
			item.find('input.sys_menu_parent').val(receiver.find('input.sys_menu_unid:first').val())
			item.find('a.ui-icon-refresh:first').show();
			item.draggable('destroy');
			item.appendTo(list).fadeIn();
			
			delete list;
			delete receiver;
		});
	}

	function moveOut(item) {
		item.fadeOut(function() {
			if (item.parent().children().size() < 3) {
				set_dragable(item.parent().parent().parent());
			}
			
			item.find('input.sys_menu_parent').val('');
			item.find('a.ui-icon-refresh:last').hide();
			item.draggable('enable');
			set_dragable(item);
			item.appendTo(gallery).fadeIn();
		});
	}

	jQuery('ul.mm_gallery > li').click(function(ev) {
		var item = jQuery(this);
		var target = jQuery(ev.target);

		if (target.is('a.ui-icon-refresh')) {
			moveOut(item);
		}
		
		delete target;
		delete item;
		
		return false;
	});
});