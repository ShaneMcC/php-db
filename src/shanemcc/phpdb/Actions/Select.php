<?php

	namespace shanemcc\phpdb\Actions;

	use \shanemcc\phpdb\Operations\Select as SelectOp;

	class Select extends DBAction {
		private $index;

		/**
		 * Create a selection action.
		 *
		 * @param $pdo PDO instance to work on
		 * @param $table Table to start on
		 * @param $index (Default: FALSE) If set to a key id, this will set the
		 *               array keys of the rows to be the value of this.
		 *               If multiple rows have the same key, then the latest one
		 *               takes priority.
		 *               If $index is not a valid returned key then it will be
		 *               treated as FALSE.
		 */
		public function __construct($pdo, $table, $index = FALSE) {
			parent::__construct($pdo, $table);
			$this->index = $index;
		}

		public static function action() { return 'SELECT'; }

		/**
		 * Build the query to execute.
		 *
		 * @return Array [$query, $params] of built query.
		 */
		public function build() {
			list($query, $params) = parent::build();

			$query = sprintf('SELECT %s FROM %s %s', implode(', ', $this->getOperations(SelectOp::operation())), $this->getTable(), $query);

			// Return the query and it's params!
			return [$query, $params];
		}

		/**
		 * Execute this action
		 *
		 * @return Array of matching rows.
		 */
		public function execute() {
			list($query, $params) = $this->build($this);

			$statement = $this->getPDO()->prepare($query);
			$result = $statement->execute($params);
			$rows = [];
			if ($result) {
				$fetch = $statement->fetchAll(\PDO::FETCH_ASSOC);
				if ($this->index !== FALSE) {
					foreach ($fetch as $row) {
						if (!array_key_exists($this->index, $row)) {
							$rows = $fetch;
							continue;
						}

						$rows[$row[$this->index]] = $row;
					}
				} else {
					$rows = $fetch;
				}
			} else {
				$this->lastError = $statement->errorInfo();
			}

			return $rows;
		}
	}
