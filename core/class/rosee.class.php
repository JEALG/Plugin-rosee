<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/rosee.inc.php';

class rosee extends eqLogic
{
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */
    public static function cron5($_eqlogic_id = null)
    {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add(__CLASS__, 'debug', '================= CRON 5 ==================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron10($_eqlogic_id = null)
    {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add(__CLASS__, 'debug', '================= CRON 10 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron15($_eqlogic_id = null)
    {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add(__CLASS__, 'debug', '================= CRON 15 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron30()
    {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add(__CLASS__, 'debug', '================= CRON 30 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cronHourly($_eqlogic_id = null)
    {
        //no both cron30 and cronHourly enabled:
        if (config::byKey('functionality::cron30::enable', 'rosee', 0) == 1) {
            config::save('functionality::cronHourly::enable', 0, 'rosee');
            return;
        }
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add(__CLASS__, 'debug', '================= CRON HEURE =================');
                $rosee->getInformations();
            }
        }
    }
    // Template
    public static function templateWidget()
    {
        return rosee_Template::getTemplate();
    }
    public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'binary', $Template = null, $unite = null, $generic_type = null, $IsVisible = 1, $icon = 'default', $forceLineB = 'default', $valuemin = 'default', $valuemax = 'default', $_order = null, $IsHistorized = '0', $repeatevent = false, $_iconname = null, $_calculValueOffset = null, $_historizeRound = null, $_noiconname = null)
    {

        $Command = $this->getCmd(null, $_logicalId);
        if (!is_object($Command)) {
            log::add(__CLASS__, 'debug', '│ Name : ' . $Name . ' -- Type : ' . $Type . ' -- LogicalID : ' . $_logicalId . ' -- Template Widget / Ligne : ' . $Template . '/' . $forceLineB . '-- Type de générique : ' . $generic_type . ' -- Icône : ' . $icon . ' -- Min/Max : ' . $valuemin . '/' . $valuemax . ' -- Calcul/Arrondi : ' . $_calculValueOffset . '/' . $_historizeRound . ' -- Ordre : ' . $_order);
            $Command = new roseeCmd();
            $Command->setId(null);
            $Command->setLogicalId($_logicalId);
            $Command->setEqLogic_id($this->getId());
            $Command->setName($Name);

            $Command->setType($Type);
            $Command->setSubType($SubType);

            if ($Template != null) {
                $Command->setTemplate('dashboard', $Template);
                $Command->setTemplate('mobile', $Template);
            }

            if ($unite != null && $SubType == 'numeric') {
                $Command->setUnite($unite);
            }

            $Command->setIsVisible($IsVisible);
            $Command->setIsHistorized($IsHistorized);

            if ($icon != 'default') {
                $Command->setdisplay('icon', '<i class="' . $icon . '"></i>');
            }
            if ($forceLineB != 'default') {
                $Command->setdisplay('forceReturnLineBefore', 1);
            }
            if ($_iconname != 'default') {
                $Command->setdisplay('showIconAndNamedashboard', 1);
            }
            if ($_noiconname != null) {
                $Command->setdisplay('showNameOndashboard', 0);
            }

            if ($_calculValueOffset != null) {
                $Command->setConfiguration('calculValueOffset', $_calculValueOffset);
            }

            if ($_historizeRound != null) {
                $Command->setConfiguration('historizeRound', $_historizeRound);
            }
            if ($generic_type != null) {
                $Command->setGeneric_type($generic_type);
            }

            if ($repeatevent == true && $Type == 'info') {
                $Command->setconfiguration('repeatEventManagement', 'never');
                log::add(__CLASS__, 'debug', '│ No Repeat pour l\'info avec le nom : ' . $Name);
            }
            if ($valuemin != 'default') {
                $Command->setconfiguration('minValue', $valuemin);
            }
            if ($valuemax != 'default') {
                $Command->setconfiguration('maxValue', $valuemax);
            }

            $Command->save();
        }

        if ($_order != null) {
            $Command->setOrder($_order);
        }

        $Command->save();

        $createRefreshCmd = true;
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = cmd::byEqLogicIdCmdName($this->getId(), __('Rafraichir', __FILE__));
            if (is_object($refresh)) {
                $createRefreshCmd = false;
            }
        }
        if ($createRefreshCmd) {
            if (!is_object($refresh)) {
                $refresh = new roseeCmd();
                $refresh->setLogicalId('refresh');
                $refresh->setIsVisible(1);
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
        }
        return $Command;
    }

    /*     * *********************Methode d'instance************************* */
    public function refresh()
    {
        foreach ($this->getCmd() as $cmd) {
            $s = print_r($cmd, 1);
            log::add(__CLASS__, 'debug', 'refresh  cmd: ' . $s);
            $cmd->execute();
        }
    }

    public function preInsert()
    {
    }

    public function postInsert()
    {
    }

    public function preSave()
    {
    }

    public function postSave()
    {
        $_eqName = $this->getName();
        log::add(__CLASS__, 'debug', 'postSave() =>' . $_eqName);
        $order = 1;

        /*  ********************** Calcul *************************** */
        $calcul = $this->getConfiguration('type_calcul');
        if (version_compare(jeedom::version(), "4", "<")) {
            $templatecore_V4 = null;
        } else {
            $templatecore_V4  = 'core::';
        };
        if ($calcul == 'tendance') {
            $td_num_max = 5;
            $td_num_visible = 1;
            $td_num = 1;
            $template_td = $templatecore_V4 . 'multiline';
            $template_td_num = 'rosee::tendance';
            $_iconname_td = 1;
            $_iconname_td_num = 1;
        } else {
            $td_num_max = 3;
            $td_num_visible = 0;
            $td_num = 1;
            $template_td = $templatecore_V4 . 'multiline';
            $template_td_num = $templatecore_V4 . 'line';
            $_iconname_td = 1;
            $_iconname_td_num = null;
        }

        $Equipement = eqlogic::byId($this->getId());

        if ($calcul == 'rosee_givre' || $calcul == 'givre' || $calcul == 'humidityabs') {
            $Equipement->AddCommand('Humidité absolue', 'humidityabs', 'info', 'numeric', $templatecore_V4 . 'line', 'g/m3', 'WEATHER_HUMIDITY', 1, 'default', 'default', 'default', 'default', $order, '0', true, 'default', null, 2, null);
            $order++;
        }

        if ($calcul == 'rosee_givre' || $calcul == 'rosee') {
            $Equipement->AddCommand('Alerte rosée', 'alert_1', 'info', 'binary', $templatecore_V4 . 'line', null, 'SIREN_STATE', 1, 'default', 'default', 'default', 'default', $order, '0', true, 'default', null, null, null);
            $order++;
        }

        if ($calcul == 'rosee_givre' || $calcul == 'rosee' || $calcul == 'givre') {
            $Equipement->AddCommand('Point de rosée', 'rosee', 'info', 'numeric', $templatecore_V4 . 'line', '°C', 'GENERIC_INFO', 1, 'default', 'default', 'default', 'default', $order, '0', true, 'default', null, 2, null);
            $order++;
        }

        if ($calcul == 'rosee_givre' || $calcul == 'givre') {
            $Equipement->AddCommand('Alerte givre', 'alert_2', 'info', 'binary', $templatecore_V4 . 'line', null, 'SIREN_STATE', 1, 'default', 'default', 'default', 'default', $order, '0', true, 'default', null, null, null);
            $order++;
            $Equipement->AddCommand('Point de givrage', 'givrage', 'info', 'numeric', $templatecore_V4 . 'line', '°C', 'GENERIC_INFO', 1, 'default', 'default', 'default', 'default', $order, '0', true, 'default', null, 2, null);
            $order++;
        }
        if ($calcul == 'rosee_givre' || $calcul == 'rosee' || $calcul == 'givre' || $calcul == 'tendance') {
            $Equipement->AddCommand('Message', 'td', 'info', 'string', $template_td, null, 'WEATHER_CONDITION', $td_num, 'default', 'default', 'default', 'default', $order, '0', true, $_iconname_td, null, null, null);
            $order++;
            $Equipement->AddCommand('Message numérique', 'td_num', 'info', 'numeric', $template_td_num, null, 'GENERIC_INFO', $td_num_visible, 'default', 'default', '0', $td_num_max, $order, '0', true, $_iconname_td_num, null, null, null);
        }
    }

    public function preUpdate()
    {
        if (!$this->getIsEnable()) return;

        if ($this->getConfiguration('type_calcul') == '') {
            throw new Exception(__('Le champ "Calcul" ne peut être vide', __FILE__));
            log::add(__CLASS__, 'error', '│ Configuration : Méthode de Calcul inexistant : ' . $this->getConfiguration('type_calcul'));
        }
    }

    public function postUpdate()
    {
        $this->getInformations();
    }

    public function preRemove()
    {
    }

    public function postRemove()
    {
    }

    public function getImage()
    {
        if ($this->getConfiguration('type_calcul') != '') {
            $filename = 'plugins/rosee/core/config/img/' . $this->getConfiguration('type_calcul') . '.png';
            if (file_exists(__DIR__ . '/../../../../' . $filename)) {
                return $filename;
            }
        }
        return 'plugins/rosee/plugin_info/rosee_icon.png';
    }

    /*  **********************Getteur Setteur*************************** */
    public function getInformations()
    {
        if (!$this->getIsEnable()) return;

        $_eqName = $this->getName();
        log::add(__CLASS__, 'debug', '┌───────── CONFIGURATION EQUIPEMENT : ' . $_eqName);

        /*  ********************** Calcul *************************** */
        $calcul = $this->getConfiguration('type_calcul');
        if ($calcul == '') {
            throw new Exception(__('Le champ "Calcul" ne peut être vide', __FILE__));
            log::add(__CLASS__, 'error', '│ Configuration : Méthode de Calcul inexistant : ' . $this->getConfiguration('type_calcul'));
        }
        log::add(__CLASS__, 'debug', '│ Méthode de calcul : ' . $calcul);

        /*  ********************** TEMPERATURE *************************** */
        $idvirt = str_replace("#", "", $this->getConfiguration('temperature'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $temperature = $cmdvirt->execCmd();
            log::add(__CLASS__, 'debug', '│ Température : ' . $temperature . ' °C');
        } else {
            if ($calcul != 'tendance') {
                throw new Exception(__('Le champ "Température" ne peut être vide', __FILE__));
                log::add(__CLASS__, 'error', '│ Configuration : Température inexistante : ' . $this->getConfiguration('temperature'));
            }
        }

        /*  ********************** Offset Température *************************** */
        if ($calcul != 'tendance') {
            $OffsetT = $this->getConfiguration('OffsetT');
            if ($OffsetT == '') {
                $OffsetT = 0;
            } else {
                $temperature = $temperature + $OffsetT;
            }
            log::add(__CLASS__, 'debug', '│ Température avec Offset : ' . $temperature . ' °C' . ' - Offset Température : ' . $OffsetT . ' °C');
        }

        /*  ********************** PRESSION *************************** */
        $pressure = $this->getConfiguration('pression');
        if ($pressure == '' && $calcul != 'tendance') { //valeur par défaut de la pression atmosphérique : 1013.25 hPa
            $pressure = 1013.25;
            log::add(__CLASS__, 'debug', '│ Pression Atmosphérique aucun équipement sélectionné, valeur par défaut : ' . $pressure . ' hPa');
        } else {
            $pressureID = str_replace("#", "", $this->getConfiguration('pression'));
            $cmdvirt = cmd::byId($pressureID);
            if (is_object($cmdvirt)) {
                $pressure = $cmdvirt->execCmd();
                log::add(__CLASS__, 'debug', '│ Pression Atmosphérique : ' . $pressure . ' hPa');
            } else {
                throw new Exception(__('Le champ "Pression Atmosphérique" ne peut être vide', __FILE__));
                log::add(__CLASS__, 'error', '│ Configuration : Pression Atmosphérique inexistante : ' . $this->getConfiguration('pression'));
            }
        }

        /*  ********************** HUMIDITE *************************** */
        $idvirt = str_replace("#", "", $this->getConfiguration('humidite'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $humidity = $cmdvirt->execCmd();
            log::add(__CLASS__, 'debug', '│ Humidité Relative : ' . $humidity . ' %');
        } else {
            if ($calcul != 'tendance') {
                throw new Exception(__('Le champ "Humidité Relative" ne peut être vide', __FILE__));
                log::add(__CLASS__, 'error', '│ Configuration : Humidité Relative  inexistante : ' . $this->getConfiguration('humidite'));
            }
        }


        /*  ********************** SEUIL D'ALERTE ROSEE *************************** */
        if ($calcul == 'rosee' || $calcul == 'rosee_givre' || $calcul == 'givre') {
            $dpr = $this->getConfiguration('DPR');
            if ($dpr == '') {
                $dpr = 2.0;
            }
            log::add(__CLASS__, 'debug', '│ Seuil DPR : ' . $dpr . ' °C');
        }

        /*  ********************** SEUIL D'HUMIDITE ABSOLUE ***************************  */
        if ($calcul == 'givre' || $calcul == 'rosee_givre') {
            $SHA = $this->getConfiguration('SHA');
            if ($SHA == '') {
                $SHA = 2.8;
            }
            log::add(__CLASS__, 'debug', '│ Seuil d\'Humidité Absolue : ' . $SHA . '');
        }
        log::add(__CLASS__, 'debug', '└─────────');

        /*  ********************** Conversion (si Besoin) *************************** */

        /*  ********************** Calcul de l'humidité absolue *************************** */
        if ($calcul == 'rosee_givre' || $calcul == 'givre' || $calcul == 'humidityabs') {
            log::add(__CLASS__, 'debug', '┌───────── CALCUL DE L\'HUMIDITE ABSOLUE : ' . $_eqName);
            $humidityabs_m3 = rosee::getHumidity($temperature, $humidity, $pressure);
            log::add(__CLASS__, 'debug', '│ Humidité Absolue : ' . $humidityabs_m3 . ' g/m3');
            log::add(__CLASS__, 'debug', '└─────────');
        }

        /*  ********************** Calcul de la tendance *************************** */
        if ($calcul == 'tendance') {
            log::add(__CLASS__, 'debug', '┌───────── CALCUL DE LA TENDANCE : ' . $_eqName);
            $va_result_T = rosee::getTendance($pressureID);
            // Partage des données du tableau
            $td_num = $va_result_T[0];
            $td = $va_result_T[1];
            log::add(__CLASS__, 'debug', '└─────────');
        }

        /*  ********************** Calcul du Point de rosée *************************** */
        $alert_1  = 0;
        if ($calcul == 'rosee_givre' || $calcul == 'rosee' || $calcul == 'givre') {
            log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE ROSEE : ' . $_eqName);
            $va_result_R = rosee::getRosee($temperature, $humidity, $dpr);
            // Partage des données du tableau
            $rosee_point = $va_result_R[0];
            $alert_1 = $va_result_R[1];
            $rosee = $va_result_R[2];
            if ($calcul == 'rosee_givre' || $calcul == 'rosee') {
                log::add(__CLASS__, 'debug', '│ Etat alerte rosée : ' . $alert_1 . ' - Point de Rosée : ' . $rosee_point . ' °C');
            } else {
                log::add(__CLASS__, 'debug', '│ Pas de mise à jour du point de  l\'alerte rosée car le calcul est désactivé');
            }
            log::add(__CLASS__, 'debug', '└─────────');
        }

        /*  ********************** Calcul du Point de givrage *************************** */
        if ($calcul == 'rosee_givre' || $calcul == 'givre') {
            log::add(__CLASS__, 'debug', '┌───────── CALCUL DU POINT DE GIVRAGE : ' . $_eqName);
            $va_result_G = rosee::getGivre($temperature, $SHA, $humidityabs_m3, $rosee);
            // Partage des données du tableau
            $td_num = $va_result_G[0];
            $td = $va_result_G[1];
            $alert_2  = $va_result_G[2];
            $frost_point  = $va_result_G[3];
            $msg_givre2 = $va_result_G[4];
            $msg_givre3 = $va_result_G[5];

            log::add(__CLASS__, 'debug', '│ Cas Actuel N°' . $td_num . ' - Alerte givre : ' . $alert_2 . ' - Message : ' . $td);
            log::add(__CLASS__, 'debug', '│ Point de Givrage : ' . $frost_point . ' °C');
            if ($msg_givre2 != '' && $msg_givre3 != '') {
                log::add(__CLASS__, 'debug', '│ ' . $msg_givre2 . ' - ' . $msg_givre3);
            };
            if ($alert_2 == 1 && $alert_1 == 1) {
                $alert_1 = 0;
                log::add(__CLASS__, 'debug', '│ Annulation alerte rosée : ' . $alert_1);
            };
            log::add(__CLASS__, 'debug', '└───────');
        } else {
            $alert_2 = 0;
            $frost_point = 5;
        };

        /*  ********************** Mise à Jour des équipements *************************** */
        log::add(__CLASS__, 'debug', '┌───────── MISE A JOUR : ' . $_eqName);

        $Equipement = eqlogic::byId($this->getId());
        if (is_object($Equipement) && $Equipement->getIsEnable()) {

            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "alert_1":
                            log::add(__CLASS__, 'debug', '│ ┌───────── ROSEE : Alerte');
                            log::add(__CLASS__, 'debug', '│ │ Etat Alerte Rosée : ' . $alert_1);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $alert_1);
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "alert_2":
                            log::add(__CLASS__, 'debug', '│ ┌───────── GIVRE : Alerte');
                            log::add(__CLASS__, 'debug', '│ │ Etat Alerte Rosée : ' . $alert_2);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $alert_2);
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "givrage":
                            log::add(__CLASS__, 'debug', '│ ┌───────── GIVRE : Point de Givre');
                            log::add(__CLASS__, 'debug', '│ │ Point de givrage : ' . $frost_point . ' °C');
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $frost_point);
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "humidityabs":
                            log::add(__CLASS__, 'debug', '│ ┌───────── HUMIDITE ABSOLUE');
                            log::add(__CLASS__, 'debug', '│ │ Humidité Absolue : ' . $humidityabs_m3 . ' g/m3');
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $humidityabs_m3);
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "rosee":
                            log::add(__CLASS__, 'debug', '│ ┌───────── ROSEE : Point de rosée');
                            log::add(__CLASS__, 'debug', '│ │ Point de Rosée : ' . $rosee_point . ' °C');
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $rosee_point);
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "td":
                            log::add(__CLASS__, 'debug', '│ ┌───────── MESSAGE : Alerte format texte');
                            if (isset($td)) {
                                if ($calcul == 'tendance') {
                                    $start_log_td = ' │ Tendance : ';
                                } else {
                                    $start_log_td = ' │ Message Alerte givre : ';
                                }
                                log::add(__CLASS__, 'debug', '│' . $start_log_td . $td);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $td);
                            } else {
                                log::add(__CLASS__, 'debug', '│ Problème avec la variable td non déclaré ');
                            }
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                        case "td_num":
                            log::add(__CLASS__, 'debug', '│ ┌───────── MESSAGE : Alerte format numérique');
                            if (isset($td_num)) {
                                if ($calcul == 'tendance') {
                                    $start_log_td = ' │ Tendance numérique : ';
                                } else {
                                    $start_log_td = ' │ Message Alerte givre numérique : ';
                                }
                                log::add(__CLASS__, 'debug', '│' . $start_log_td . $td_num);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $td_num);
                            } else {
                                log::add(__CLASS__, 'debug', '│ Problème avec la variable td_num non déclaré ');
                            }
                            log::add(__CLASS__, 'debug', '│ └─────────');
                            break;
                    }
                }
            }
        }
        log::add(__CLASS__, 'debug', '└─────────');
        log::add(__CLASS__, 'debug', '================ FIN CRON =================');
        return;
    }
    /*  ********************** Calcul de l'humidité absolue *************************** */
    public static function getHumidity($temperature, $humidity, $pressure)
    {
        $terme_pvs1 = 2.7877 + (7.625 * $temperature) / (241.6 + $temperature);
        log::add(__CLASS__, 'debug', '│ terme_pvs1 : ' . $terme_pvs1);
        $pvs = pow(10, $terme_pvs1);
        log::add(__CLASS__, 'debug', '│ Pression de saturation de la vapeur d\'eau (pvs) : ' . $pvs);
        $pv = ($humidity * $pvs) / 100.0;
        log::add(__CLASS__, 'debug', '│ Pression partielle de vapeur d\'eau (pv) : ' . $pv);
        $humi_a = 0.622 * ($pv / (($pressure * 100.0) - $pv));
        log::add(__CLASS__, 'debug', '│ Humidité absolue en kg d\'eau par kg d\'air : ' . $humi_a . ' kg');
        $v = (461.24 * (0.622 + $humi_a) * ($temperature + 273.15)) / ($pressure * 100.0);
        log::add(__CLASS__, 'debug', '│ Volume specifique (v) : ' . $v . ' m3/kg');
        $p = 1.0 / $v;
        log::add(__CLASS__, 'debug', '│ Poids spécifique (p) : ' . $p . ' m3/kg');
        $humidityabs_m3 = 1000.0 * $humi_a * $p;
        return $humidityabs_m3;
    }

    /*  ********************** Calcul du Point de rosée *************************** */
    public static function getRosee($temperature, $humidity, $dpr)
    {
        /* Paramètres de MAGNUS pour l'air saturé (entre -45°C et +60°C) : */
        $alpha = 6.112;
        $beta = 17.62;
        $lambda = 243.12;
        log::add(__CLASS__, 'debug', '│ Paramètres de MAGNUS pour l\'air saturé (entre -45°C et +60°C) : Lambda = ' . $lambda . ' °C // alpha = ' . $alpha . ' hPa // beta = ' . $beta);

        $Terme1 = log($humidity / 100);
        $Terme2 = ($beta * $temperature) / ($lambda + $temperature);
        log::add(__CLASS__, 'debug', '│ Terme1 = ' . $Terme1 . ' // Terme2 = ' . $Terme2);
        $rosee = $lambda * ($Terme1 + $Terme2) / ($beta - $Terme1 - $Terme2);
        $rosee_point = $rosee;
        $alert_1 = 0;

        /*  ********************** Calcul de l'alerte rosée en fonction du seuil d'alerte *************************** */
        $frost_alert_rosee = $temperature - $rosee_point;
        log::add(__CLASS__, 'debug', '│ Calcul point de rosée : (Température - point de Rosée) : (' . $temperature . ' - ' . $rosee_point . ' )= ' . $frost_alert_rosee . ' °C');
        if ($frost_alert_rosee <= $dpr) {
            $alert_1 = 1;
            log::add(__CLASS__, 'debug', '│ Résultat : Calcul Alerte point de rosée = (' . $frost_alert_rosee . ' <= ' . $dpr . ') = Alerte active');
        } else {
            log::add(__CLASS__, 'debug', '│ Résultat : Calcul Alerte point de rosée = (' . $frost_alert_rosee . ' > ' . $dpr . ') = Alerte désactivée');
        }

        return array($rosee_point, $alert_1, $rosee);
    }
    /*  ********************** Calcul du Point de givrage *************************** */
    public static function getGivre($temperature, $SHA, $humidityabs_m3, $rosee)
    {
        $td = 'Aucun risque de Givre';
        $td_num = number_format(0);
        $alert_2  = 0;
        if ($temperature <= 5) {
            $msg_givre2 = '';
            $msg_givre3 = '';
            $frost_K = 2954.61 / ($temperature + 273.15);
            $frost_K = $frost_K + 2.193665 * log(($temperature + 273.15));
            $frost_K = $frost_K - 13.3448;
            $frost_K = 2671.02 / $frost_K;
            $frost_K = $frost_K + ($rosee + 273.15) - ($temperature + 273.15);
            log::add(__CLASS__, 'debug', '│ Point de givrage : ' . $frost_K . ' K');
            $frost = $frost_K - 273.15;
            $frost_point = $frost;

            if ($temperature <= 1 && $frost_point <= 0) {
                $alert_2  = 1;
                if ($humidityabs_m3 > $SHA) { // Cas N°3
                    $td = 'Givre, Présence de givre';
                    $td_num = number_format(3);
                };
                if ($humidityabs_m3 < $SHA) { // Cas N°1
                    $td = 'Givre peu probable malgré la température';
                    $td_num = number_format(1);
                };
            } elseif ($temperature <= 4 && $frost_point <= 0.5) { // Cas N°2
                $td = 'Risque de givre';
                $td_num = number_format(2);
                $alert_2  = 1;
                //} else {// Cas N°0
            };
        } else {
            $frost_point = 5;
            $msg_givre2 = 'Info supplémentaire : Il fait trop chaud pas de calcul de l\'alerte givre (' . $temperature . ' °C > 5 °C)';
            $msg_givre3 = 'Info supplémentaire : Point de givre fixé est : ' . $frost_point . ' °C';
        };
        return array($td_num, $td, $alert_2, $frost_point, $msg_givre2, $msg_givre3);
    }
    /*  ********************** Calcul de la tendance *************************** */
    public static function getTendance($pressureID)
    {
        $histo = new scenarioExpression();
        $endDate = $histo->collectDate($pressureID);

        // calcul du timestamp actuel
        log::add('rosee', 'debug', '│ ┌─────── Timestamp -15min');
        $_date1 = new DateTime("$endDate");
        $_date2 = new DateTime("$endDate");
        $startDate = $_date1->modify('-15 minute');
        $startDate = $_date1->format('Y-m-d H:i:s');
        log::add(__CLASS__, 'debug', '│ │ Start Date : ' . $startDate);
        log::add(__CLASS__, 'debug', '│ │ End Date : ' . $endDate);

        // dernière mesure barométrique
        $h1 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add(__CLASS__, 'debug', '│ │ Pression Atmosphérique : ' . $h1 . ' hPa');
        log::add(__CLASS__, 'debug', '│ └───────');

        // calcul du timestamp - 2h
        log::add(__CLASS__, 'debug', '│ ┌─────── Timestamp -2h');
        $endDate = $_date2->modify('-2 hour');
        $endDate = $_date2->format('Y-m-d H:i:s');
        $startDate = $_date1->modify('-2 hour');
        $startDate = $_date1->format('Y-m-d H:i:s');
        log::add(__CLASS__, 'debug', '│ │ Start Date : ' . $startDate);
        log::add(__CLASS__, 'debug', '│ │ End Date : ' . $endDate);

        // mesure barométrique -2h
        $h2 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add(__CLASS__, 'debug', '│ │ Pression Atmosphérique : ' . $h2 . ' hPa');

        // calculs de tendance 15min/2h
        if ($h2 != null) {
            $td2h = ($h1 - $h2) / 2;
            log::add(__CLASS__, 'debug', '│ │ Tendance -2h : ' . $td2h . ' hPa/h');
        } else {
            $td2h = 0;
            log::add(__CLASS__, 'debug', '│ │ Pression Atmosphérique -2h nulle (historique) : ' . $h2 . ' hPa');
        }
        log::add(__CLASS__, 'debug', '│ └───────');

        // calcul du timestamp - 4h
        log::add('rosee', 'debug', '│ ┌─────── Timestamp -4h');
        $endDate = $_date2->modify('-2 hour');
        $endDate = $_date2->format('Y-m-d H:i:s');
        $startDate = $_date1->modify('-2 hour');
        $startDate = $_date1->format('Y-m-d H:i:s');
        log::add(__CLASS__, 'debug', '│ │ Start Date : ' . $startDate);
        log::add(__CLASS__, 'debug', '│ │ End Date : ' . $endDate);

        // mesure barométrique -4h
        $h4 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add('rosee', 'debug', '│ │ Pression Atmosphérique : ' . $h4 . ' hPa');

        // calculs de tendance 2h/4h
        if ($h4 != null) {
            $td4h = (($h1 - $h4) / 4);
            log::add(__CLASS__, 'debug', '│ │ Tendance -4h : ' . $td4h . ' hPa/h');
        } else {
            $td4h = 0;
            log::add(__CLASS__, 'debug', '│ │ Pression Atmosphérique -4h nulle (historique) : ' . $h4 . ' hPa');
        }
        log::add(__CLASS__, 'debug', '│ └───────');

        // calculs de tendance
        log::add(__CLASS__, 'debug', '│ ┌───────── Calcul Tendance Moyenne');
        // sources : http://www.freescale.com/files/sensors/doc/app_note/AN3914.pdf
        // et : https://www.parallax.com/sites/default/files/downloads/29124-Altimeter-Application-Note-501.pdf

        // moyennation de la tendance à -2h (50%) et -4h (50%)
        $td_moy = (0.5 * $td2h + 0.5 * $td4h);
        $dPdT = number_format($td_moy, 3, '.', '');
        log::add(__CLASS__, 'debug', '│ │ Tendance Moyenne (dPdT): ' . $dPdT . ' hPa/h');

        if ($td_moy > 2.5) { // Quickly rising High Pressure System, not stable
            $td = 'Forte embellie, instable';
            $td_num = number_format(5);
        } elseif ($td_moy > 0.5 && $td_moy <= 2.5) { // Slowly rising High Pressure System, stable good weather
            $td = 'Amélioration, beau temps durable';
            $td_num = number_format(4);
        } elseif ($td_moy > 0.0 && $td_moy <= 0.5) { // Stable weather condition
            $td = 'Lente amélioration, temps stable';
            $td_num = number_format(3);
        } elseif ($td_moy > -0.5 && $td_moy <= 0) { // Stable weather condition
            $td = 'Lente dégradation, temps stable';
            $td_num = number_format(2);
        } elseif ($td_moy > -2.5 && $td_moy <= -0.5) { // Slowly falling Low Pressure System, stable rainy weather
            $td = 'Dégradation, mauvais temps durable';
            $td_num = number_format(1);
        } else { // Quickly falling Low Pressure, Thunderstorm, not stable
            $td = 'Forte dégradation, instable';
            $td_num = 0;
        };
        log::add(__CLASS__, 'debug', '│ └─────────');
        return array($td_num, $td);
    }
}

class roseeCmd extends cmd
{
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */
    public function dontRemoveCmd()
    {
        if ($this->getLogicalId() == 'refresh') {
            return true;
        }
        return false;
    }

    public function execute($_options = null)
    {
        if ($this->getLogicalId() == 'refresh') {
            log::add('rosee', 'debug', ' ─────────> ACTUALISATION MANUELLE');
            $this->getEqLogic()->getInformations();
            log::add('rosee', 'debug', ' ─────────> FIN ACTUALISATION MANUELLE');
            return;
        }
    }
}
