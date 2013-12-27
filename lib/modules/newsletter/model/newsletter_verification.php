<?php 
namespace Podlove\Modules\Newsletter\Model;

use \Podlove\Model\Base;

class NewsletterVerification extends Base
{	
	
}

NewsletterVerification::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
NewsletterVerification::property( 'name', 'TEXT' );
NewsletterVerification::property( 'verification_hash', 'TEXT' );
NewsletterVerification::property( 'email', 'TEXT' );
NewsletterVerification::property( 'subscription_date', 'DATETIME' );
NewsletterVerification::property( 'IP', 'VARCHAR(255)' );