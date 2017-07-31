<?php

	namespace shanemcc\phpdb;

	interface DBChanger {
		public function getChanges();
		public function getVersionField();
	}
