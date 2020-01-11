<?php namespace Chitanka\PermissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends Controller {

	const PARAM_ROLE = 'role';

	public function indexAction($username) {
		$user = $this->loadUser($username);
		$managingUser = $this->getManagingUser();
		$master = $this->getMaster();
		return $this->render('ChitankaPermissionBundle:Default:index.html.twig', [
			'user' => $user,
			'manager' => $managingUser,
			'revokableRoles' => $master->getRevokableRolesForUser($user, $managingUser),
			'grantableRoles' => $master->getGrantableRolesForUser($user, $managingUser),
		]);
	}

	public function grantRoleAction(Request $request, $username) {
		$role = $request->get(self::PARAM_ROLE);
		$this->assertManagerCanManageRole($role);
		$user = $this->loadUser($username);
		$this->getMaster()->grantRole($user, $role, $this->getManagingUser());
		return $this->redirectToIndex($user);
	}

	public function revokeRoleAction(Request $request, $username) {
		$role = $request->get(self::PARAM_ROLE);
		$this->assertManagerCanManageRole($role);
		$user = $this->loadUser($username);
		$this->getMaster()->revokeRole($user, $role, $this->getManagingUser());
		return $this->redirectToIndex($user);
	}

	private function redirectToIndex(UserInterface $user) {
		return $this->redirectToRoute('chitanka_permission_index', ['username' => $user->getUsername()]);
	}

	/**
	 * @param string $username
	 * @return \App\Entity\User|UserInterface
	 */
	private function loadUser($username) {
		return $this->getDoctrine()->getRepository('App:User')->loadUserByUsername($username);
	}

	private function getManagingUser() {
		return $this->getUser();
	}

	/** @return \Chitanka\PermissionBundle\Domain\Master */
	private function getMaster() {
		return $this->get(\Chitanka\PermissionBundle\Domain\Master::class);
	}

	private function assertManagerCanManageRole($role) {
		if (!$this->getMaster()->canManageRole($this->getManagingUser(), $role)) {
			throw new \Exception("Access denied");
		}
	}
}
