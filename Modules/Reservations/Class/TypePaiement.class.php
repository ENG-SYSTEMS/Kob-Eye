<?php

class TypePaiement extends genericClass {

    /**
     * Retourne un plugin Boutique / TypePaiement
     * @return	Implémentation d'interface
     */
    public function getPlugin() {
        $plugin = Plugin::createInstance('Reservations','TypePaiement', $this->Plugin);
        $plugin->setConfig( $this->PluginConfig );
        return $plugin;
    }


}