<?php

use Elastica\Document;
use Elastica\Type\Mapping;

/**
 * A root extension package with one or more versions.
 */
class ExtensionPackage extends DataObject {

	public static $db = array(
		'Name' => 'Varchar(255)',
		'Description' => 'Text',
		'Type' => 'Varchar(100)',
		'Readme' => 'HTMLText',
		'Released' => 'SS_Datetime',
		'Repository' => 'Varchar(255)',
		'Downloads' => 'Int',
		'LastUpdated' => 'SS_Datetime',
		'LastBuilt' => 'SS_Datetime'
	);

	public static $has_one = array(
		'Vendor' => 'ExtensionVendor'
	);

	public static $has_many = array(
		'Versions' => 'ExtensionVersion'
	);

	public static $many_many = array(
		'Keywords' => 'ExtensionKeyword',
		'CompatibleVersions' => 'SilverStripeVersion'
	);

	public static $extensions = array(
		'SilverStripe\\Elastica\\Searchable'
	);

	/**
	 * @return string
	 */
	public function getVendorName() {
		return substr($this->Name, 0, strpos($this->Name, '/'));
	}

	public function getTypeIcon() {
		switch ($this->Type) {
			case 'module': return 'icon-gift';
			case 'theme': return 'icon-picture';
			default: return 'icon-question-sign';
		}
	}

	public function Link() {
		return Controller::join_links(
			Director::baseURL(), 'extensions', $this->Name
		);
	}

	public function getElasticaMapping() {
		return new Mapping(null, array(
			'name' => array('type' => 'string'),
			'description' => array('type' => 'string'),
			'type' => array('type' => 'string'),
			'silverstripes' => array('type' => 'string', 'index_name' => 'silverstripe'),
			'vendor' => array('type' => 'string'),
			'tags' => array('type' => 'string', 'index_name' => 'tag')
		));
	}

	public function getElasticaDocument() {
		return new Document($this->ID, array(
			'name' => $this->Name,
			'description' => $this->Description,
			'type' => $this->Type,
			'silverstripes' => $this->CompatibleVersions()->column('Name'),
			'vendor' => $this->getVendorName(),
			'tags' => $this->Keywords()->column('Name'),
			'_boost' => sqrt($this->Downloads)
		));
	}

}
