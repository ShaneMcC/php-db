<?php

	namespace shanemcc\phpdb;

	/**
	 * This class is a wrapper around the Operations/Actions classes.
	 */
	class Search extends Actions\DBAction {
		/** Initial fields to select. */
		protected $_fields;

		/**
		 * Create a selection action.
		 *
		 * @param $pdo PDO instance to work on
		 * @param $table Table to start on
		 * @param $fields Initial fields to select
		 */
		public function __construct($pdo, $table, $fields, $index = FALSE) {
			parent::__construct($pdo, $table);
			$this->_fields = $fields;

			// Specific keys to get
			foreach ($fields as $key) {
				$this->addOperation(new Operations\Select($table, $key, $key));
			}
		}

		/**
		 * Find some rows from the database.
		 *
		 * @param $fields Fields to look for (Array of field => value)
		 * @param $comparators (Optional Array) Comparators to use for fields.
		 * @return FALSE if we were unable to find rows, else an array of rows.
		 */
		public function searchRows($searchFields, $comparators = []) {
			foreach ($searchFields as $key => $value) {
				$comparator = isset($comparators[$key]) ? $comparators[$key] : null;

				$this->where($key, $value, $comparator);
			}

			$rows = $this->getRows();
			return (count($rows) == 0) ? FALSE : $rows;
		}

		/**
		 * Add a where item to the search.
		 *
		 * @param $key Key to search for.
		 * @param $value Value to search for.
		 * @param $comparator (Default: '=') Comparator to use.
		 * @return $this for chaining.
		 */
		public function where($key, $value, $comparator = null) {
			return $this->addOperation(new Operations\Where($key, $value, $comparator));
		}

		/**
		 * Add a or where to the search.
		 *
		 * @param $items Array of ([$key, $value] or [$key, $value, $comparator]) items
		 * @return $this for chaining.
		 */
		public function whereOr($items) {
			$operations = [];

			foreach ($items as $item) {
				if (count($item) == 1) { continue; }
				else if (count($item) == 2) { $item[] = null; }
				else if (count($item) > 3) { continue; }

				[$key, $value, $comparator] = $item;

				$operations[] = new Operations\Where($key, $value, $comparator);
			}

			return $this->addOperation(new Operations\WhereOr($operations));
		}

		/**
		 * Add an order clause.
		 *
		 * @param $key Key to order by
		 * @param $direction Direction to order (Default: 'ASC')
		 * @return $this for chaining.
		 */
		public function order($key, $direction = 'ASC') {
			return $this->addOperation(new Operations\Order($key, $direction));
		}

		/**
		 * Add a limit
		 *
		 * @param $limit Limit returned results.
		 * @param $offset (Optional) Optional offset.
		 * @return $this for chaining.
		 */
		public function limit($limit, $offset = FALSE) {
			return $this->addOperation(new Operations\Limit($limit, $offset));
		}

		/**
		 * Add an extra colum to select (eg from a join).
		 *
		 * This will not allow you to select a column AS a name that exists in
		 * the original fields that were passed, as these are all selected AS
		 * their own name.
		 *
		 * @param $table Table key is in.
		 * @param $key Key is in.
		 * @param $as (Default: $key) name to select this column as
		 * @return $this for chaining.
		 */
		public function select($table, $key, $as = null) {
			if (!in_array($as, $this->_fields)) {
				$this->addOperation(new Operations\Select($table, $key, $as));
			}
			return $this;
		}

		/**
		 * Add a join
		 *
		 * @param $table Table to join to
		 * @param $on (Default: none) Statement to join on
		 * @param $direction (Default: none) LEFT/RIGHT join.
		 * @return $this for chaining.
		 */
		public function join($table, $on = null, $direction = null) {
			return $this->addOperation(new Operations\Join($table, $on, $direction));
		}

		/**
		 * Get rows based on this object.
		 *
		 * @param $index (Default: FALSE) If set to a key id, this will set the
		 *               array keys of the rows to be the value of this.
		 *               If multiple rows have the same key, then the latest one
		 *               takes priority.
		 *               If $index is not a valid returned key then it will be
		 *               treated as FALSE.
		 * @return Array of matching rows.
		 */
		public function getRows($index = FALSE) {
			return (new Actions\Select($this->getPDO(), $this->getTable(), $index))->addOperations($this->getOperations('*'))->execute();
		}

		/**
		 * Delete rows based on this object.
		 *
		 * @return True/False if operation was a success.
		 */
		public function delete() {
			return (new Actions\Delete($this->getPDO(), $this->getTable()))->addOperations($this->getOperations('*'))->execute();
		}

		// Filler methods.
		public static function action() { throw new \Exception('Search::action() is not implemented.'); }
		public function execute() { throw new \Exception('Search::execute() is not implemented.'); }
	}
