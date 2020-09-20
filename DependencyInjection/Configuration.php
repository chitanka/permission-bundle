<?php namespace Chitanka\PermissionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface {

	const TRANSITIONS = 'transitions';
	const TRANSITIONS_FROM = 'from';
	const TRANSITIONS_TO = 'to';
	const TRANSITIONS_MANAGERS = 'managers';

	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder('chitanka_permission');
		$treeBuilder->getRootNode()
			->children()
				->arrayNode(self::TRANSITIONS)
					->arrayPrototype()
						->children()
							->scalarNode(self::TRANSITIONS_FROM)->end()
							->scalarNode(self::TRANSITIONS_TO)->end()
							->arrayNode(self::TRANSITIONS_MANAGERS)
								->beforeNormalization()->castToArray()->end()
								->scalarPrototype()
							->end()
						->end()
					->end()
				->end()
			->end();
		return $treeBuilder;
	}
}
