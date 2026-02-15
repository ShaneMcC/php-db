<?php

	namespace shanemcc\phpdb\Operations;

	class WhereJsonExtract extends DBOperation {
		private $column;
		private $jsonPath;
		private $comparator;
		private $value;
		private $where;
		private $params;

		/**
		 * Create a WHERE clause using JSON_EXTRACT.
		 *
		 * @param $column Column containing JSON data.
		 * @param $jsonPath JSON path key to extract (top-level key name).
		 * @param $value Value to compare against.
		 * @param $comparator (Default: '=') Comparator to use.
		 */
		public function __construct($column, $jsonPath, $value, $comparator = null) {
			$this->column = $column;
			$this->jsonPath = $jsonPath;
			$this->comparator = $comparator;
			$this->value = $value;

			$this->build();
		}

		public function __toString() {
			return $this->where;
		}

		public function getParams() {
			return $this->params;
		}

		public function build($paramPrefix = '') {
			$comp = ($this->comparator === null ? '=' : $this->comparator);

			// Sanitize the JSON path to prevent SQL injection.
			// Only allow alphanumeric, underscore, and hyphen characters.
			$safePath = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->jsonPath);

			$paramName = sprintf(':%sjson_%s_%s', $paramPrefix, $this->column, $safePath);

			$this->where = sprintf(
				'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`%s`, \'$.%s\'))) %s LOWER(%s)',
				$this->column,
				$safePath,
				$comp,
				$paramName
			);

			$this->params = [$paramName => $this->value];
		}

		public static function operation() { return 'WHERE'; }
	}
