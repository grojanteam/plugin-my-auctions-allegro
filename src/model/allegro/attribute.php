<?php
declare(strict_types=1);

class GJMAA_Model_Allegro_Attribute extends GJMAA_Model
{
	protected $tableName = 'gjmaa_allegro_attributes';

	protected $defaultPK = 'attribute_id';

	protected $columns = [
		'attribute_id' => [
			'schema' => [
				'INT',
				'AUTO_INCREMENT',
				'NOT NULL'
			],
			'format' => '%d'
		],
		'attribute_category_allegro_id' => [
			'schema' => [
				'TEXT',
				'NULL'
			],
			'format' => '%s'
		],
		'attribute_allegro_id' => [
			'schema' => [
				'INT',
				'NOT NULL'
			],
			'format' => '%d'
		],
		'attribute_name' => [
			'schema' => [
				'VARCHAR (255)',
				'NOT NULL'
			],
			'format' => '%s'
		],
		'attribute_type' => [
			'schema' => [
				'VARCHAR (255)',
				'NOT NULL'
			],
			'format' => '%s'
		],
		'attribute_required' => [
			'schema' => [
				'SMALLINT',
				'NOT NULL'
			],
			'format' => '%d'
		],
		'attribute_options' => [
			'schema' => [
				'TEXT',
				'NULL'
			],
			'format' => '%s'
		],
		'attribute_dictionary' => [
			'schema' => [
				'TEXT',
				'NULL'
			],
			'format' => '%s'
		],
		'attribute_restrictions' => [
			'schema' => [
				'TEXT',
				'NULL'
			],
			'format' => '%s'
		]
	];

	public function update($version)
	{
		if (version_compare($version, '2.6.3') < 0) {
			$this->uninstall();
			$this->install();
		}
	}

	public function loadByAttributeAndCategory($attributeAllegroId, $categoryAllegroId)
	{
		return $this->load([
			$attributeAllegroId,
			$categoryAllegroId
		], [
			'attribute_allegro_id',
			'attribute_category_allegro_id'
		]);
	}

	public function loadByCategoryId($categoryAllegroId)
	{
		$filters = [
			'WHERE' => sprintf('attribute_category_allegro_id = %s', $categoryAllegroId)
		];

		return $this->getAllBySearch(
			$filters,
			100
		);
	}

	public function saveMultiple($data)
	{
		$query = "INSERT INTO {$this->getTable()} VALUES ";
		$query .= '(';
		foreach($this->getColumns() as $columnName => $columnData) {
			$query .= $columnData['format'] . ',';
		}
		$query = rtrim($query,',');
		$query .= ')';

		foreach($data as $item) {
			$this->getWpdb()->query($this->getWpdb()->prepare($query, $item['attribute_id'], $item['attribute_category_allegro_id'], $item['attribute_allegro_id'], $item['attribute_name'], $item['attribute_type'], $item['attribute_required'], $item['attribute_options'], $item['attribute_dictionary'], $item['attribute_restrictions']));
		}
	}
}