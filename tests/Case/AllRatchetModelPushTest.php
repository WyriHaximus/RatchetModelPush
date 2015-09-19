<?php
/**
 * All RatchetModelPush plugin tests
 */
class AllRatchetModelPushTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All RatchetModelPush test');

		$path = CakePlugin::path('RatchetModelPush') . 'tests' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
