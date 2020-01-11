<?php namespace Chitanka\PermissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class PermissionLog {

	const TYPE_INFO = '';
	const TYPE_GRANT = '+';
	const TYPE_REVOKE = '-';

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * The name of the manager who has granted or revoked a role
	 * @var string
	 * @ORM\Column(type="string", length=50)
	 */
	private $manager;

	/**
	 * The name of the user who has been granted role or whose role has been revoked
	 * @var string
	 * @ORM\Column(type="string", length=50)
	 */
	private $user;

	/**
	 * The name of the role which has been granted or revoked
	 * @var string
	 * @ORM\Column(type="string", length=20)
	 */
	private $role;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $message;

	/**
	 * Grant, revoke, or something else. See the TYPE constansts.
	 * @var string
	 * @ORM\Column(type="string", length=1)
	 */
	private $type;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	public function __construct(string $manager, string $user, string $role, string $type = null, string $message = null) {
		$this->manager = $manager;
		$this->user = $user;
		$this->role = $role;
		$this->type = $type ?? self::TYPE_INFO;
		$this->message = $message;
		$this->createdAt = new \DateTime();
	}

	public static function createForGranting(string $manager, string $user, string $role, string $message = null) {
		return new self($manager, $user, $role, self::TYPE_GRANT, $message);
	}

	public static function createForRevoking(string $manager, string $user, string $role, string $message = null) {
		return new self($manager, $user, $role, self::TYPE_REVOKE, $message);
	}

	public function getId(): int {
		return $this->id;
	}

	public function getManager(): string {
		return $this->manager;
	}

	public function getUser(): string {
		return $this->user;
	}

	public function getRole(): string {
		return $this->role;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getCreatedAt(): \DateTime {
		return $this->createdAt;
	}

}
