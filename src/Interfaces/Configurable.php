<?php
namespace Ulysse\Base\Interfaces;

use Ulysse\Base\Configuration\Configuration;

/**
 * L'interface Configurable permet d'affecter un Configuration à un objet.
 *
 * @author orodriguez
 */
interface Configurable
{

	/**
	 * Récupère la configuration par défaut de la classe.
	 *
	 * @return Configuration
	 */
	public static function getDefaultConfiguration(): Configuration;

	/**
	 * Récupère la configuration actuelle de l'objet.
	 *
	 * Cette configuration doit pouvoir être utilisée pour modifier la configuration de l'objet.
	 * Néanmoins l'objet en lui même ne pourra être notifié de la modification de ladite configuration.
	 * Ainsi seul les paramètres de configuration utilisés par les futurs appels de méthodes de l'objet pourront être impactés par une modification
	 * (Un mécanisme optionnel d'observer devrait être mis en place à l'avenir, sans doute sur une classe dérivée) de Configuration.
	 *
	 * @return Configuration
	 */
	public function getConfiguration(): Configuration;
}