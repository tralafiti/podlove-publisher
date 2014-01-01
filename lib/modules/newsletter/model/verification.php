<?php 
namespace Podlove\Modules\Newsletter\Model;

use \Podlove\Model\Base;

class Verification extends Base
{	
	
}

Verification::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Verification::property( 'verification_hash', 'TEXT' );
Verification::property( 'email', 'TEXT' );
Verification::property( 'subscription_date', 'DATETIME' );
Verification::property( 'IP', 'VARCHAR(255)' );