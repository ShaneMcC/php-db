<?php

	namespace shanemcc\phpdb\Actions;
	use \shanemcc\phpdb\Operations\DBOperation;
	use \shanemcc\phpdb\Operations\Select as SelectOp;
	use \shanemcc\phpdb\Operations\Join;
	use \shanemcc\phpdb\Operations\Where;
	use \shanemcc\phpdb\Operations\Order;
	use \shanemcc\phpdb\Operations\Limit;

	abstract class DBAction {
		/** PDO object to search in. */
		private $_pdo;

		/** Main table to search in. */
		protected $_table;

		/** last error for this object. */
		private $lastError = NULL;

		/** Operations. */
		private $operations = [];

		public function __construct($pdo, $table) {
			$this->_pdo = $pdo;
			$this->_table = $table;
		}

		/**
		 * Add an array of operations to this query.
		 *
		 * @param $operations Array of operations to add.
		 * @return $this for chaining.
		 */
		public function addOperations($operations) {
			foreach ($operations as $o) {
				if ($o instanceof DBOperation) {
					$this->addOperation($o);
				} else if (is_array($o)) {
					$this->addOperations($o);
				}
			}

			return $this;
		}

		/**
		 * Add an operation to this query.
		 *
		 * @param $operation Operation to add
		 * @return $this for chaining.
		 */
		public function addOperation($operation) {
			$type = $operation->operation();

			if (!array_key_exists($type, $this->operations)) {
				$this->operations[$type] = [];
			}

			$this->operations[$type][] = $operation;

			return $this;
		}

		/**
		 * Get all operations of a given type.
		 *
		 * @param $operation Operation type to get.
		 * @return Array of operations of the given type.
		 */
		public function getOperations($type) {
			if ($type == '*') {
				return $this->operations;
			} else if (array_key_exists($type, $this->operations)) {
				return $this->operations[$type];
			} else {
				return [];
			}
		}

		/**
		 * Get the last error we encountered with the database.
		 *
		 * @return last error.
		 */
		public function getLastError() {
			return $this->lastError;
		}

		/**
		 * Get our table.
		 *
		 * @return Our table
		 */
		protected function getTable() {
			return $this->_table;
		}

		/**
		 * Get our PDO Object.
		 *
		 * @return Our PDO Object
		 */
		protected function getPDO() {
			return $this->_pdo;
		}

		/**
		 * Build the query to execute.
		 *
		 * @return Array [$query, $params] of built query.
		 */
		public function build() {
			$query = '';
			$params = [];

			// Add in any joins.
			if (count($this->getOperations(Join::operation())) > 0) {
				$query .= sprintf(' %s', implode(' ', $this->getOperations(Join::operation())));
			}

			// WHERE Data.
			$whereFields = $this->getOperations(Where::operation());

			foreach ($whereFields as $field) {
				$params = array_merge($params, $field->getParams());
			}

			// WHERE clause.
			if (count($whereFields) > 0) {
				$query .= sprintf(' WHERE %s', implode(' AND ', $whereFields));
			}

			// Add Ordering
			if (count($this->getOperations(Order::operation())) > 0) {
				$query .= sprintf(' ORDER BY %s', implode(', ', $this->getOperations(Order::operation())));
			}

			// Add LIMIT
			if (count($this->getOperations(Limit::operation())) > 0) {
				$query .= (String)($this->getOperations(Limit::operation())[0]);
			}

			// Return the query and it's params!
			return [$query, $params];
		}

		public abstract static function action();

		/**
		 * Execute this action
		 *
		 * @return Array of matching rows.
		 */
		public abstract function execute();
	}
