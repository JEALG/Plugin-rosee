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

class rosee extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */
    public static function cron5() {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 5 ==================');
                $rosee->getInformations();
            }
        }
    }
    public static function cron30($_eqlogic_id = null) {
        //no both cron5 and cron30 enabled:
        if (config::byKey('functionality::cron5::enable', 'rosee', 0) == 1) {
            config::save('functionality::cron30::enable', 0, 'rosee');
            return;
        }
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 30 =================');
                $rosee->getInformations();
            }
        }
    }

    /*     * *********************Methode d'instance************************* */
    public function refresh() {
        foreach ($this->getCmd() as $cmd) {
            $s = print_r($cmd, 1);
            log::add('rosee', 'debug', 'refresh  cmd: '.$s);
            $cmd->execute();
        }
    }

    public function preUpdate() {
        if (!$this->getIsEnable()) return;

        if ($this->getConfiguration('temperature') == '') {
            throw new Exception(__('Le champ "Température" ne peut être vide',__FILE__));
        }

        if ($this->getConfiguration('humidite') == '') {
            throw new Exception(__('Le champ "Humidité Relative" ne peut être vide',__FILE__));
        }

        if ($this->getConfiguration('type_calcul') == '') {
            $this->setConfiguration('type_calcul', 'rosee_givre');
        }
    }
    public function postInsert() {

    }

    public function postSave(){
        log::add('rosee', 'debug', 'postSave()');
        $order = 1;
        /*  ********************** Calcul *************************** */
        $calcul=$this->getConfiguration('type_calcul');

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new roseeCmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
        }
        $refresh->setEqLogic_id($this->getId());
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->save();

        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            $roseeCmd = $this->getCmd(null, 'humidite_absolue');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Humidité absolue', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('humidite_absolue');
                $roseeCmd->setConfiguration('data', 'humidite_a');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','WEATHER_HUMIDITY');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('g/m3');
            $roseeCmd->setLogicalId('humidite_absolue');
            $roseeCmd->setType('info');
            $roseeCmd->setSubType('numeric');
            $roseeCmd->save();
        }

        if ($calcul=='rosee_givre'|| $calcul=='rosee') {
            $roseeCmd = $this->getCmd(null, 'alerte_rosee');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Alerte rosée', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('alerte_rosee');
                $roseeCmd->setConfiguration('data', 'alert_r');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('binary');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->save();
        }

        if ($calcul=='rosee_givre'|| $calcul=='rosee' || $calcul=='givre') {
            $roseeCmd = $this->getCmd(null, 'rosee');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Point de rosée', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('rosee');
                $roseeCmd->setConfiguration('data', 'rosee_point');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('numeric');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('°C');
            $roseeCmd->save();
        }

        if ($calcul=='rosee_givre'|| $calcul=='givre') {
            $roseeCmd = $this->getCmd(null, 'alerte_givre');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Alerte givre', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('alerte_givre');
                $roseeCmd->setConfiguration('data', 'alert_g');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('binary');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->save();

            $roseeCmd  = $this->getCmd(null, 'givrage');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Point de givrage', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('givrage');
                $roseeCmd->setConfiguration('data', 'frost_point');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('numeric');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('°C');
            $roseeCmd->save();

            $roseeCmd = $this->getCmd(null, 'message_givre');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Message', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('message_givre');
                $roseeCmd->setConfiguration('data', 'message_givre');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('string');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(0);
                $roseeCmd->setDisplay('generic_type','WEATHER_CONDITION');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->save();

            $roseeCmd = $this->getCmd(null, 'message_givre_num');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Message numérique', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('message_givre_num');
                $roseeCmd->setConfiguration('data', 'message_givre_num');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('numeric');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(0);
                $roseeCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->setConfiguration('minValue', 0);
            $roseeCmd->setConfiguration('maxValue', 3);
            $roseeCmd->save();
        }
    }

    /*  **********************Getteur Setteur*************************** */
    public function postUpdate() {
        foreach (eqLogic::byType('rosee') as $rosee) {
            $rosee->getInformations();
        }
    }

    public function getInformations() {
        if (!$this->getIsEnable()) return;

        $_eqName = $this->getName();
        log::add('rosee', 'debug', '┌───────── CONFIGURATION EQUIPEMENT : '.$_eqName );

        /*  ********************** TEMPERATURE *************************** */
        $idvirt = str_replace("#","",$this->getConfiguration('temperature'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $temperature = $cmdvirt->execCmd();
            log::add('rosee', 'debug', '│ Température : ' . $temperature.' °C');
        } else {
            log::add('rosee', 'error', '│ Configuration : Température inexistante : ' . $this->getConfiguration('temperature'));
        }

        /*  ********************** Offset Température *************************** */
        $OffsetT=$this->getConfiguration('OffsetT');
        if ($OffsetT== '') {
            $OffsetT=0;
            log::add('rosee', 'debug', '│ Aucun Offset Température : ' . $OffsetT.'');
        } else {
            log::add('rosee', 'debug', '│ Offset Température : ' . $OffsetT.'');
            $temperature = $temperature + $OffsetT;
            log::add('rosee', 'debug', '│ Température avec Offset : ' .$temperature.' °C');
        }

        /*  ********************** PRESSION *************************** */
        $pression = $this->getConfiguration('pression');
        if ($pression == '') {//valeur par défaut de la pression atmosphérique : 1013.25 hPa
            $pression=1013.25;
            log::add('rosee', 'debug', '│ Pression Atmosphérique aucun équipement sélectionné');
            log::add('rosee', 'debug', '│ Pression Atmosphérique par défaut : ' . $pression. ' hPa');
        } else {
            $idvirt = str_replace("#","",$this->getConfiguration('pression'));
            $cmdvirt = cmd::byId($idvirt);
            if (is_object($cmdvirt)) {
                $pression = $cmdvirt->execCmd();
                log::add('rosee', 'debug', '│ Pression Atmosphérique : ' . $pression.' hPa');
            } else {
                log::add('rosee', 'error', '│ Configuration : Pression Atmosphérique inexistante : ' . $this->getConfiguration('pression'));
            }
        }

        /*  ********************** HUMIDITE *************************** */
        $idvirt = str_replace("#","",$this->getConfiguration('humidite'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $humidite = $cmdvirt->execCmd();
            log::add('rosee', 'debug', '│ Humidité Relative : ' . $humidite.' %');
        } else {
            log::add('rosee', 'error', '│ Configuration : Humidité Relative  inexistante : ' . $this->getConfiguration('humidite'));
        }

        /*  ********************** Calcul *************************** */
        $calcul=$this->getConfiguration('type_calcul');
        if ($calcul== '') {
            $calcul='rosee_givre';
            log::add('rosee', 'debug', '│ Aucune méthode de calcul sélectionnée');
        }
        log::add('rosee', 'debug', '│ Méthode de calcul : ' . $calcul);

        /*  ********************** SEUIL D'ALERTE ROSEE *************************** */
        $dpr=$this->getConfiguration('DPR');
        if ($dpr == '') {
            $dpr=2.0;
            log::add('rosee', 'debug', '│ Seuil DPR : Aucune valeur de saisie => Valeur par défaut : '. $dpr.' °C');
        } else {
            log::add('rosee', 'debug', '│ Seuil DPR : ' . $dpr.' °C');
        }

        /*  ********************** SEUIL D'HUMIDITE ABSOLUE ***************************  */
        $SHA=$this->getConfiguration('SHA');
        if ($SHA == '') {
            $SHA=2.8;
            log::add('rosee', 'debug', '│ Seuil d\'Humidité Absolue : Aucune valeur de saisie => Valeur par défaut : ' . $SHA.'');
        } else {
            log::add('rosee', 'debug', '│ Seuil d\'Humidité Absolue : ' . $SHA.'');
        }
        log::add('rosee', 'debug', '└─────────');

        /*  ********************** Conversion (si Besoin) *************************** */

        /*  ********************** Calcul de l'humidité absolue *************************** */
        log::add('rosee', 'debug', '┌───────── CALCUL DE L HUMIDITE ABSOLUE : '.$_eqName);
        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            $humi_a_m3 = rosee::getHumidity($temperature, $humidite,$pression);
            log::add('rosee', 'debug', '│ Humidité Absolue : ' . $humi_a_m3.' g/m3');
        } else {
            log::add('rosee', 'debug', '│ Pas de calcul de l\'Humidité Absolue');
        }
        log::add('rosee', 'debug', '└─────────');

        /*  ********************** Calcul du Point de rosée *************************** */
        log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE ROSEE : '.$_eqName);
        $alert_r  = 0;
        if ($calcul=='rosee_givre'|| $calcul=='rosee' || $calcul=='givre' ) {
            $va_result_R = rosee::getRosee($temperature, $humidite, $dpr);
            // Partage des données du tableau
            $rosee_point = $va_result_R [0];
            $alert_r = $va_result_R [1];
            $rosee = $va_result_R [2];

            if ($calcul=='rosee_givre'|| $calcul=='rosee') {
                log::add('rosee', 'debug', '│ Etat alerte rosée : ' . $alert_r);
                log::add('rosee', 'debug', '│ Point de Rosée : ' . $rosee_point .' °C');
            } else {
                log::add('rosee', 'debug', '│ Pas de mise à jour du point de  l\'alerte rosée car le calcul est désactivé');
            }
        } else {
            log::add('rosee', 'debug', '│ Pas de calcul du point de rosée et de l\'alerte  ');
        }
        log::add('rosee', 'debug', '└─────────');

        /*  ********************** Calcul du Point de givrage *************************** */
        log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE GIVRAGE : '.$_eqName);
        if ($calcul=='rosee_givre'|| $calcul=='givre' ) {
            $va_result_G = rosee::getGivre($temperature, $SHA, $humi_a_m3, $rosee);
            // Partage des données du tableau
            $msg_givre_num = $va_result_G [0];
            $msg_givre = $va_result_G [1];
            $alert_g  = $va_result_G [2];
            $frost_point  = $va_result_G [3];
            $msg_givre2 = $va_result_G [4];
            $msg_givre3 = $va_result_G [5];

            log::add('rosee', 'debug', '│ ┌─────── Cas Actuel N°'.$msg_givre_num . ' / Alerte givre : ' .$alert_g );
            log::add('rosee', 'debug', '│ │ Message : ' .$msg_givre );
            log::add('rosee', 'debug', '│ │ Point de Givrage : ' . $frost_point.' °C');
            if ($msg_givre2 != '' && $msg_givre3 != ''){
                log::add('rosee', 'debug', $msg_givre2 );
                log::add('rosee', 'debug', $msg_givre3 );
            };
            if ($alert_g == 1 && $alert_r == 1) {
                $alert_r = 0;
                log::add('rosee', 'debug', '│ │ Annulation alerte rosée : ' .$alert_r );
            };
            log::add('rosee', 'debug', '│ └───────');
        } else {
            $alert_g = 0;
            $frost_point = 5;
            log::add('rosee', 'debug', '│ Pas de calcul du point de givrage et de l\'alerte');
        };
        log::add('rosee', 'debug', '└─────────');

        /*  ********************** Mise à Jour des équipements *************************** */
        log::add('rosee', 'debug', '┌───────── MISE A JOUR : '.$_eqName);

        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            $cmd = $this->getCmd('info', 'humidite_absolue');//Mise à jour de l'équipement Humidité absolue
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $humi_a_m3);
                $cmd->save();
                $cmd->event($humi_a_m3);
                log::add('rosee', 'debug', '│ ┌───────── HUMIDITE ABSOLUE');
                log::add('rosee', 'debug', '│ │ Humidité Absolue : ' . $humi_a_m3.' g/m3');
                log::add('rosee', 'debug', '│ └─────────');
            };
        };

        if ($calcul=='rosee_givre'|| $calcul=='rosee'){
            $cmd = $this->getCmd('info', 'alerte_rosee');//Mise à jour de l'équipement Alerte rosée
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $alert_r);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($alert_r);
                log::add('rosee', 'debug', '│ ┌───────── ROSEE');
                log::add('rosee', 'debug', '│ │ Alerte Rosée : ' . $alert_r);
            };
        };

        if ($calcul=='rosee_givre'|| $calcul=='rosee' || $calcul=='givre') {
            $cmd = $this->getCmd('info', 'rosee');//Mise à jour de l'équipement point de rosée
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $rosee_point);
                $cmd->save();
                $cmd->event($rosee_point);
                if ( $calcul=='givre') {
                    log::add('rosee', 'debug', '│ ┌───────── ROSEE');
                };
                log::add('rosee', 'debug', '│ │ Point de Rosée : ' . $rosee_point.' °C');
            };
            log::add('rosee', 'debug', '│ └─────────');
        };

        if ($calcul=='rosee_givre'|| $calcul=='givre' ) {
            $cmd = $this->getCmd('info', 'alerte_givre');//Mise à jour de l'équipement Alerte givre
            if (is_object($cmd)) {
                $cmd->setConfiguration('value', $alert_g);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($alert_g);
                log::add('rosee', 'debug', '│ ┌───────── GIVRE');
                log::add('rosee', 'debug', '│ │ Alerte Givre : ' . $alert_g);
            };

            $cmd = $this->getCmd('info', 'givrage');//Mise à jour de l'équipement Givrage
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $frost_point);
                $cmd->save();
                $cmd->event($frost_point);
                log::add('rosee', 'debug', '│ │ Point de givrage : ' . $frost_point.' °C');
            }

            $cmd = $this->getCmd('info', 'message_givre'); //Mise à jour de l'équipement message
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $msg_givre);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($msg_givre);
                log::add('rosee', 'debug', '│ │ Message Alerte givre : ' . $msg_givre);
            }

            $cmd = $this->getCmd('info', 'message_givre_num'); //Mise à jour de l'équipement message
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $msg_givre_num);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($msg_givre_num);
                log::add('rosee', 'debug', '│ │ Message Alerte givre numérique : ' . $msg_givre_num);
            };
            log::add('rosee', 'debug', '│ └─────────');
        } else{
            // $cmd = $this->getCmd('info', 'alerte_givre');
            //if (is_object($cmd)) {
            //  $cmd->setConfiguration('value', $alert_g);
            //$cmd->remove();
            //$cmd->save();
            // log::add('rosee', 'debug', '│ ┌───────── GIVRE');
            //log::add('rosee', 'debug', '│ │ Suppression : ' );
            // };
        };
        log::add('rosee', 'debug', '└─────────');
        log::add('rosee', 'debug', '================ FIN CRON =================');
        return;
    }
    /*  ********************** Calcul de l'humidité absolue *************************** */
    public function getHumidity($temperature, $humidite, $pression) {
        $terme_pvs1 = 2.7877 + (7.625 * $temperature) / (241.6 + $temperature);
        log::add('rosee', 'debug', '│ terme_pvs1 : ' . $terme_pvs1);
        $pvs = pow(10,$terme_pvs1);
        log::add('rosee', 'debug', '│ Pression de saturation de la vapeur d\'eau (pvs) : ' . $pvs);
        $pv = ($humidite * $pvs) / 100.0;
        log::add('rosee', 'debug', '│ Pression partielle de vapeur d\'eau (pv) : ' . $pv);
        $humi_a = 0.622 * ($pv / (($pression * 100.0) - $pv));
        log::add('rosee', 'debug', '│ Humidité absolue en kg d\'eau par kg d\'air : ' . $humi_a .' kg');
        $v = (461.24 * (0.622 + $humi_a) * ($temperature +273.15)) / ($pression * 100.0);
        log::add('rosee', 'debug', '│ Volume specifique (v) : ' . $v .' m3/kg');
        $p = 1.0 / $v;
        log::add('rosee', 'debug', '│ Poids spécifique (p) : ' . $p.' m3/kg');
        $humi_a_m3 = 1000.0 * $humi_a * $p;
        $humi_a_m3 = round(($humi_a_m3), 1);
        return $humi_a_m3;
    }

    /*  ********************** Calcul du Point de rosée *************************** */
    public function getRosee($temperature, $humidite, $dpr) {
        /* Paramètres de MAGNUS pour l'air saturé (entre -45°C et +60°C) : */
        $alpha = 6.112;
        $beta = 17.62;
        $lambda = 243.12;
        log::add('rosee', 'debug', '│ Paramètres de MAGNUS pour l\'air saturé (entre -45°C et +60°C) : Lambda = ' . $lambda .' °C // alpha = ' . $alpha .' hPa // beta = ' . $beta );

        $Terme1 = log($humidite/100);
        $Terme2 = ($beta * $temperature) / ($lambda + $temperature);
        log::add('rosee', 'debug', '│ Terme1 = ' . $Terme1 .' // Terme2 = ' . $Terme2 );
        $rosee = $lambda * ($Terme1 + $Terme2) / ($beta - $Terme1 - $Terme2);
        $rosee_point = round(($rosee), 1);
        $alert_r = 0;

        /*  ********************** Calcul de l'alerte rosée en fonction du seuil d'alerte *************************** */
        $frost_alert_rosee = $temperature - $rosee_point;
        log::add('rosee', 'debug', '│ Calcul point de rosée : (Température - point de Rosée) : (' .$temperature .' - '.$rosee_point .' )= ' . $frost_alert_rosee .' °C');
        if ($frost_alert_rosee <= $dpr) {
            $alert_r = 1;
            log::add('rosee', 'debug', '│ Résultat : Calcul Alerte point de rosée = (' .$frost_alert_rosee .' <= ' .$dpr .') = Alerte active');
        } else {
            log::add('rosee', 'debug', '│ Résultat : Calcul Alerte point de rosée = (' .$frost_alert_rosee .' > ' .$dpr .') = Alerte désactivée');
        }

        return array($rosee_point, $alert_r,$rosee);
    }
    /*  ********************** Calcul du Point de givrage *************************** */
    public function getGivre($temperature, $SHA, $humi_a_m3, $rosee) {
        $msg_givre = 'Aucun risque de Givre';
        $msg_givre_num = 0;
        $alert_g  = 0;
        if ($temperature <= 5  ) {
            $msg_givre2 ='';
            $msg_givre3 ='';
            $frost_K = 2954.61 / ($temperature + 273.15);
            $frost_K = $frost_K + 2.193665 * log(($temperature + 273.15));
            $frost_K = $frost_K - 13.3448;
            $frost_K = 2671.02 / $frost_K;
            $frost_K = $frost_K + ($rosee + 273.15) - ($temperature + 273.15);
            log::add('rosee', 'debug', '│ Point de givrage : ' . $frost_K.' K');
            $frost = $frost_K -273.15;
            $frost_point = round(($frost), 1);

            if($temperature <= 1 && $frost_point <= 0) {
                $alert_g  = 1;
                if ($humi_a_m3 > $SHA) {// Cas N°3
                    $msg_givre = 'Givre, Présence de givre';
                    $msg_givre_num = 3;
                };
                if ($humi_a_m3 < $SHA) {// Cas N°1
                    $msg_givre = 'Givre peu probable malgré la température';
                    $msg_givre_num = 1;
                };
            } elseif ($temperature <= 4 && $frost_point <= 0.5) {// Cas N°2
                $msg_givre = 'Risque de givre';
                $msg_givre_num = 2;
                $alert_g  = 1;
            //} else {// Cas N°0
            };
        } else {
            $frost_point = 5;
            $msg_givre2 ='│ │ Info supplémentaire : Il fait trop chaud pas de calcul de l\'alerte givre (' .$temperature .' °C > 5 °C)';
            $msg_givre3 ='│ │ Info supplémentaire : Point de givre fixé est : ' .$frost_point .' °C';
        };
        return array ($msg_givre_num, $msg_givre, $alert_g, $frost_point,$msg_givre2 ,$msg_givre3);
    }
}

class roseeCmd extends cmd {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */
    public function dontRemoveCmd(){
        return true;
    }

    public function execute($_options = null) {
        if ($this->getLogicalId() == 'refresh') {
            $this->getEqLogic()->getInformations();
            return;
        }
    }
}
