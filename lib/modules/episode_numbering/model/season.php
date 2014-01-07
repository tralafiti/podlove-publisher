<?php 
namespace Podlove\Modules\EpisodeNumbering\Model;

use \Podlove\Model\Base;

class Season extends Base
{	
	
}

Season::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Season::property( 'description', 'TEXT' );
Season::property( 'number', 'VARCHAR(255)' );
Season::property( 'mnemonic', 'VARCHAR(255)' );