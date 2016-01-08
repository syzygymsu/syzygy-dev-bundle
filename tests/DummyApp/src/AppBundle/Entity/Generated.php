<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Generated {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/* ================================ CUT HERE ================================ */

	/**
	 * Set id
	 *
	 * @param integer $id
	 *
	 * @return Generated
	 */
	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

}
