<?php 
namespace Podlove\Modules\Contributors;

use \Podlove\Model\Episode;
use \Podlove\Modules\Contributors\Model\Contributor;
use \Podlove\Modules\Contributors\Model\ContributorRole;
use \Podlove\Modules\Contributors\Model\EpisodeContribution;
use \Podlove\Modules\Contributors\Model\ShowContribution;

class Contributors extends \Podlove\Modules\Base {

	protected $module_name = 'Network';
	protected $module_description = 'Support for Podcast Networks using <a href="http://codex.wordpress.org/Create_A_Network">WordPress Multisite</a> environments.';
	protected $module_group = 'system';

	public function load() {
		
	}

	

}