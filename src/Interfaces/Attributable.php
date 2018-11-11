<?php
namespace Ulysse\Base\Interfaces;

/**
 * L'interface Attributable offre un moyen d'affecter des attributs à un objet.
 *
 * Elle diffère de l'interface Configurable dans le fait qu'elle ne permet pas de représenter des
 * structures d'informations hiérarchiques.
 * L'implémentation de cette interface se veut être une alternative à Configurable
 * permettant de paramétrer des objets plus simplement et directement.
 *
 * @author orodriguez
 *
 */
interface Attributable
{

	/**
	 * Récupère la valeur d'un attribut.
	 *
	 * @param mixed $attributeType
	 * @return mixed La valeur de l'attribut.
	 */
	public function getAttribute($attribute);

	/**
	 * Affecte une valeur à un attribut.
	 *
	 * @param mixed $attribute
	 * @param mixed $value
	 * @return object L'instance de l'objet Attributable.
	 */
	public function setAttribute($attribute, $value): object;
}