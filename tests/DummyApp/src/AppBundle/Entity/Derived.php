<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Derived extends Base {

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/* ================================ CUT HERE ================================ */
}
