<?php

class TypePaiement extends genericClass {

	/**
	 * Retourne un plugin Boutique / Instance
	 * @return	Implémentation d'interface
	 */
	public function getPlugin() {
		$plugin = Plugin::createInstance('Boutique','TypePaiement', $this->Plugin);
		$plugin->setConfig( $this->PluginConfig );
		return $plugin;
	}

	
}