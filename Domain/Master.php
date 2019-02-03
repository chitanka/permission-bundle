<?php namespace Chitanka\PermissionBundle\Domain;

use Chitanka\PermissionBundle\DependencyInjection\Configuration;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\User\UserInterface;

class Master {

	const ROLE_PREFIX = 'ROLE_';

	private $em;
	private $transitions;
	private $targetRoleByManagerMap;

	public function __construct(Registry $doctrine, array $transitions) {
		$this->em = $doctrine->getManager();
		$this->transitions = $transitions;
	}

	public function canManageRole(UserInterface $manager, $role) {
		if (!isset($this->targetRoleByManagerMap)) {
			$this->buildTargetRoleByManagerMap();
		}
		$managedRoles = call_user_func_array('array_merge', array_intersect_key($this->targetRoleByManagerMap, array_flip($manager->getRoles())));
		return in_array(self::normalizeRoleName($role), $managedRoles);
	}

	public function getRevokableRolesForUser(UserInterface $user, UserInterface $manager) {
		$revokableRoles = [];
		$currentUserRoles = $user->getRoles();
		foreach ($this->transitions as $transition) {
			$targetRole = self::normalizeRoleName($transition[Configuration::TRANSITIONS_TO]);
			if (in_array($targetRole, $currentUserRoles) && $this->canManageRole($manager, $targetRole)) {
				$revokableRoles[] = $targetRole;
			}
		}
		return $revokableRoles;
	}

	public function getGrantableRolesForUser(UserInterface $user, UserInterface $manager) {
		$grantableRoles = [];
		$currentUserRoles = $user->getRoles();
		foreach ($this->transitions as $transition) {
			$startRole = self::normalizeRoleName($transition[Configuration::TRANSITIONS_FROM]);
			$targetRole = self::normalizeRoleName($transition[Configuration::TRANSITIONS_TO]);
			if (in_array($startRole, $currentUserRoles) && !in_array($targetRole, $currentUserRoles) && $this->canManageRole($manager, $targetRole)) {
				$grantableRoles[] = $targetRole;
			}
		}
		return $grantableRoles;
	}

	public function grantRole(UserInterface $user, $role) {
		$user->addRole($role);
		$this->saveUser($user);
	}

	public function revokeRole(UserInterface $user, $role) {
		$user->removeRole($role);
		$this->saveUser($user);
	}

	public function saveUser(UserInterface $user) {
		$this->em->persist($user);
		$this->em->flush();
	}

	private function buildTargetRoleByManagerMap() {
		$this->targetRoleByManagerMap = [];
		foreach ($this->transitions as $transition) {
			foreach ($transition[Configuration::TRANSITIONS_MANAGERS] as $managerRole) {
				$this->targetRoleByManagerMap[self::normalizeRoleName($managerRole)][] = self::normalizeRoleName($transition[Configuration::TRANSITIONS_TO]);
			}
		}
	}

	public static function normalizeRoleName($role) {
		$role = strtoupper($role);
		if (strpos($role, self::ROLE_PREFIX) === false) {
			$role = self::ROLE_PREFIX.$role;
		}
		return $role;
	}

	public static function denormalizeRoleName($role) {
		return strtolower(preg_replace('/^'.self::ROLE_PREFIX.'/', '', $role));
	}

}
