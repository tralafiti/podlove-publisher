<?php
namespace Podlove\Modules\Timeline\Model;

class ExtendedAttributes extends \Podlove\Model\Base {
}

ExtendedAttributes::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
ExtendedAttributes::property( 'timeline_id', 'INT' );
ExtendedAttributes::property( 'attribute_key', 'VARCHAR(255)' );
ExtendedAttributes::property( 'attribute_value', 'TEXT' );