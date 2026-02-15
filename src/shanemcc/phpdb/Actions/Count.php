<?php

	namespace shanemcc\phpdb\Actions;

	class Count extends DBAction {

		public function __construct($pdo, $table) {
			parent::__construct($pdo, $table);
		}

		public static function action() { return 'COUNT'; }

		/**
		 * Build the query to execute.
		 *
		 * @return Array [$query, $params] of built query.
		 */
		public function build() {
			list($query, $params) = parent::build();

			$query = sprintf('SELECT COUNT(*) as `count` FROM %s %s', $this->getTable(), $query);

			return [$query, $params];
		}

		/**
		 * Execute this action.
		 *
		 * @return int Count of matching rows.
		 */
		public function execute() {
			list($query, $params) = $this->build($this);

			try {
				$statement = $this->getPDO()->prepare($query);
				$result = $statement->execute($params);
			} catch (\PDOException $t) {
				$result = FALSE;
			}

			if ($result) {
				return intval($statement->fetch(\PDO::FETCH_ASSOC)['count']);
			}

			return 0;
		}
	}
