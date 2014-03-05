<?php

/**
 * Convert a Livestatus Filter tree to a livestatus query
 */
class LivestatusFilterBuilderVisitor implements LivestatusFilterVisitor {
	/**
	 * The query name of a filter
	 */
	protected $filter = "Filter: ";
	/**
	 * The query name of a and-line
	 */
	protected $and    = "And: ";
	/**
	 * The query name of a or-line
	 */
	protected $or     = "Or: ";
	/**
	 * The query name of a negation line
	 */
	protected $not    = "Negate:";

	/**
	 * The current set this visitor is operating for
	 */
	private $set;

	/**
	 * Populate the related set for the visitor
	 *
	 * @param $set Set object
	 */
	public function __construct( $set ) {
		$this->set = $set;
	}

	/**
	 * Visit an and node
	 */
	public function visit_and( LivestatusFilterAnd $filt, $data ) {
		$subfilters = $filt->get_sub_filters();
		$result = "";
		foreach( $subfilters as $subf )
			$result .= $subf->visit($this,false);
		$count = count($subfilters);
		if( $count != 1 )
			$result .= $this->and . $count . "\n";
		return $result;
	}

	/**
	 * Visit an or node
	 */
	public function visit_or( LivestatusFilterOr $filt, $data ) {
		$subfilters = $filt->get_sub_filters();
		$result = "";
		foreach( $subfilters as $subf )
			$result .= $subf->visit($this,false);
		$count = count($subfilters);
		if( $count != 1 )
			$result .= $this->or . $count . "\n";
		return $result;
	}

	/**
	 * Visit an negation node
	 */
	public function visit_match( LivestatusFilterMatch $filt, $data ) {
		$field = $this->set->process_field_name($filt->get_field());
		$op = $filt->get_op();
		$value = $filt->get_value();
		return $this->filter . $field . " " . $op . " " . $value . "\n";
	}

	public function visit_not( LivestatusFilterNot $filt, $data ) {
		$subfilter = $filt->get_filter();
		$result = $subfilter->visit($this,false);
		$result .= $this->not . "\n";
		return $result;
	}
}
