<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 10:06 AM
 */

namespace O2System\ORM\Interfaces;


trait Hierarchical
{
	/**
	 * Rebuild Tree
	 *
	 * Rebuild self hierarchical table
	 *
	 * @access public
	 *
	 * @param string $table Working database table
	 *
	 * @return numeric  rgt column value
	 */
	final public function _after_process_rebuild( $id_parent = 0, $left = 0, $depth = 0 )
	{
		$table = empty( $table ) ? $this->table : $table;

		/* the right value of this node is the left value + 1 */
		$right = $left + 1;

		/* get all children of this node */
		$this->db->select( 'id' )->where( 'id_parent', $id_parent )->order_by( 'record_ordering' );
		$query = $this->db->get( $table );

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				/* does this page have children? */
				$right = $this->rebuild_tree( $table, $row->id, $right, $depth + 1 );
			}
		}

		/* update this page with the (possibly) new left, right, and depth values */
		$data = array( 'record_left' => $left, 'record_right' => $right, 'record_depth' => $depth - 1 );
		$this->db->update( $table, $data, [ 'id' => $id_parent ] );

		/* return the right value of this node + 1 */

		return $right + 1;
	}
}