<?php
namespace WPA;

class Attrs {

	protected $_item;
	protected $_string;

	public function __construct($item) {
		$this->_item = $item;

		$this->_string = $this->build();
	}

	public function build() {
		$attrs = [];

		if(is_user_logged_in()) {
			$mnu = [];
			$post = get_post($this->_item);
			if($post->post_type == 'nav_menu_item') {

				if($post->type == 'post_type' && $post->object == 'page') {
					$post_type_object = get_post_type_object( $post->object );
					if($post_type_object && $post_type_object->show_in_admin_bar) {
						$mnu[] = [$post_type_object->labels->edit_item, get_edit_post_link($this->_item->post_id)];
					}
				} else if($post->type == 'taxonomy') {
					$tax = get_taxonomy($post->object);
					if($tax && $tax->show_in_menu) {
						$mnu[] = [$tax->labels->edit_item, get_edit_tag_link($this->_item->object_id, $this->_item->object)];
					}
				}
			} else if($post->post_type == 'post') {
				$post_type_object = get_post_type_object( $post->post_type );
				if($post_type_object && $post_type_object->show_in_admin_bar) {
					$mnu[] = [$post_type_object->labels->edit_item, get_edit_post_link($this->_item->ID)];
				}
			} else if($post->post_type == 'page') {
				$post_type_object = get_post_type_object( $post->post_type );
				if($post_type_object && $post_type_object->show_in_admin_bar) {
					$mnu[] = [$post_type_object->labels->edit_item, get_edit_post_link($this->_item->ID)];
				}
			}
			$attrs['data-context-menu'] = json_encode($mnu);
		}


		$fixed = array();
		foreach($attrs as $k=>$v) {
			$fixed[] = $k."=\"".htmlspecialchars($v)."\"";
		}

		return " ".implode(" ", $fixed)." ";
	}

	public function __toString() {
		return $this->_string;
	}
}
