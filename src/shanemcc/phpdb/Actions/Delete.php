<?php

	namespace shanemcc\phpdb\Actions;

	class Delete extends DBAction {

		public static function action() { return 'DELETE'; }

		/**
		 * Build the query to execute.
		 *
		 * @return Array [$query, $params] of built query.
		 */
		public function build() {
			list($query, $params) = parent::build();

			$query = sprintf('DELETE FROM %s %s', $this->getTable(), $query);

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

			if ($result) {
				return TRUE;
			} else {
				$this->lastError = $statement->errorInfo();
				return FALSE;
			}
		}
	}
