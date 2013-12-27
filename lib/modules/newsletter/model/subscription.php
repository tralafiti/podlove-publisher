<?php 
namespace Podlove\Modules\Newsletter\Model;

use \Podlove\Model\Base;

class Subscription extends Base
{	
	
}

Subscription::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Subscription::property( 'name', 'TEXT' );
Subscription::property( 'unsubscribe_hash', 'TEXT' );
Subscription::property( 'email', 'TEXT' );
Subscription::property( 'subscription_date', 'DATETIME' );