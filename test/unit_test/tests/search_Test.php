<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Search_Test extends TapUnit {
	protected $controller = false; /* Controller to test */
	
	public function setUp() {
		$this->controller = new Search_Controller();
	}

	/*
	 * Those tests should test how the search from the ExpParser filter is converted to a live status query
	 * 
	 * Tests handling the syntax of the filter shoudl be in expparser_searchfilter_Test,
	 * This is about columns and generation oh the query, and wildcard
	 */
	
	/* *****
	 * Test simple table access
	 */
	public function test_host() {
		$this->run_test('h:kaka', array(
				'hosts' =>
					"Filter: name ~~ kaka\n".
					"Filter: address ~~ kaka\n".
					"Or: 2\n"
		) );
	}
	public function test_service() {
		$this->run_test('s:kaka', array(
				'services' =>
					"Filter: description ~~ kaka\n".
					"Filter: display_name ~~ kaka\n".
					"Or: 2\n"
				) );
	}
	public function test_hostgroups() {
		$this->run_test('hg:kaka', array(
				'hostgroups' =>
					"Filter: name ~~ kaka\n".
					"Filter: alias ~~ kaka\n".
					"Or: 2\n"
				) );
	}
	public function test_servicegroups() {
		$this->run_test('sg:kaka', array(
				'servicegroups' =>
					"Filter: name ~~ kaka\n".
					"Filter: alias ~~ kaka\n".
					"Or: 2\n"
				) );
	}
	
	/* ******
	 * Test wildcard search
	 */
	public function test_wildcard() {
		$this->run_test('h:aaa%bbb', array(
				'hosts' =>
					"Filter: name ~~ aaa.*bbb\n".
					"Filter: address ~~ aaa.*bbb\n".
					"Or: 2\n"
				) );
	}
	
	
	/* ******
	 * Test combined host/service (services by hosts)
	 */
	public function test_host_serivce() {
		$this->run_test('h:kaka and s:pong', array(
				'services' =>
					"Filter: description ~~ pong\n".
					"Filter: display_name ~~ pong\n".
					"Or: 2\n".
					"Filter: host_name ~~ kaka\n".
					"Filter: host_address ~~ kaka\n".
					"Or: 2\n"
		) );
	}
	
	/* ******
	 * Test limit
	 */
	public function test_host_limit() {
		$this->run_test('h:kaka limit=24', array(
				'hosts' =>
					"Filter: name ~~ kaka\n".
					"Filter: address ~~ kaka\n".
					"Or: 2\n",
				'limit' => 24
		) );
	}

	protected function run_test( $query, $expect ) {
		$result = $this->controller->queryToLSFilter( $query );
		$this->ok_eq( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
	}
}
