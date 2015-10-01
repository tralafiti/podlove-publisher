<?php
namespace Podlove\Modules\Timeline\Model;

use \Podlove\Modules\Timeline\Model\ExtendedAttributes;

class Item extends \Podlove\Model\Base {
	public static function find_all_by_type_and_episode($episode_id, $type) {
		if ( ! $episode_id || ! $type )
			return;

		return self::find_all_by_where('episode_id = ' . $episode_id . ' AND `type` LIKE \'' . $type  . '\'' );
	}

	public function get_extended_attribute($attribute_key) {
		foreach ($this->extended_attributes as $extended_attribute) {
			if ( $extended_attribute->attribute_key == $attribute_key )
				return $extended_attribute->attribute_value;
		}
	}

	protected static function find_all_by_sql($sql) {
		global $wpdb;
		
		$class = get_called_class();
		$models = array();
		
		$rows = $wpdb->get_results($sql);
		
		if ( ! $rows )
			return array();
		
		foreach ( $rows as $row ) {
			// Add Extended Attributes to Timeline item
			$row->extended_attributes = ExtendedAttributes::find_all_by_where('timeline_id = ' . $row->id );

			$model = new $class();
			$model->flag_as_not_new();
			foreach ( $row as $property => $value ) {
				$model->$property = $value;
			}

			$models[] = $model;
		}
		
		return $models;
	}

}

Item::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Item::property( 'episode_id', 'INT' );
Item::property( 'type', 'VARCHAR(255)' );
Item::property( 'cuemark', 'TINYINT(1) NULL DEFAULT 0' );
Item::property( 'title', 'TEXT' );
Item::property( 'start_time', 'FLOAT' );
Item::property( 'end_time', 'FLOAT' );
Item::property( 'position', 'INT' );