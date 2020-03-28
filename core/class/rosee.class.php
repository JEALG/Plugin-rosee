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
            foreach ($this->getCmd() as $cmd)
            {
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
                $this->setConfiguration('type_calcul', rosee_givre);
              //  $this->getConfiguration('type_calcul') == 'rosee_givre';
            //    throw new Exception(__('Le champ "Type calcul" ne peut être vide',__FILE__));
            }
        }
    
        public function postSave(){
            log::add('rosee', 'debug', 'postSave()');
            /*  ********************** Calcul *************************** */
            $calcul=$this->getConfiguration('type_calcul');
            if ($calcul== '') {
                $calcul='rosee_givre';
                log::add('rosee', 'debug', '│ Aucune méthode de calcul sélectionnée');
            }
                log::add('rosee', 'debug', ' Méthode de calcul : ' . $calcul);
            
            
            $order = 1;
            
            $refresh = $this->getCmd(null, 'refresh');
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

            $roseeHCmd = $this->getCmd(null, 'humidite_absolue');
            if (!is_object($roseeHCmd)) {
                $roseeHCmd = new roseeCmd();
                $roseeHCmd->setName(__('Humidité absolue', __FILE__));
                $roseeHCmd->setEqLogic_id($this->id);
                $roseeHCmd->setLogicalId('humidite_absolue');
                $roseeHCmd->setConfiguration('data', 'humidite_a');
                $roseeHCmd->setUnite('g/m3');
                $roseeHCmd->setIsHistorized(0);
                $roseeHCmd->setIsVisible(1);
                $roseeHCmd->setDisplay('generic_type','WEATHER_HUMIDITY');
                $roseeHCmd->setOrder($order);
                $order ++;   
            }
                $roseeHCmd->setEqLogic_id($this->getId());
                $roseeHCmd->setLogicalId('humidite_absolue');
                $roseeHCmd->setType('info');
                $roseeHCmd->setSubType('numeric');
                $roseeHCmd->save();
        
        // Ajout d'une commande pour l'alerte rosée
            $roseeARCmd = $this->getCmd(null, 'alerte_rosee');
            if (!is_object($roseeARCmd)) {
                $roseeARCmd = new roseeCmd();
                $roseeARCmd->setName(__('Alerte rosée', __FILE__));
                $roseeARCmd->setEqLogic_id($this->id);
                $roseeARCmd->setLogicalId('alerte_rosee');
                $roseeARCmd->setConfiguration('data', 'alert_r');
                $roseeARCmd->setType('info');
                $roseeARCmd->setSubType('binary');
                $roseeARCmd->setUnite('');
                $roseeARCmd->setIsHistorized(0);
                $roseeARCmd->setIsVisible(1);
                $roseeARCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeARCmd->setOrder($order);
                $order ++;
                $roseeARCmd->save();
            }
        
        // Ajout d'une commande pour le point de rosée
            $roseePRCmd = $this->getCmd(null, 'rosee');
            if (!is_object($roseePRCmd)) {
                $roseePRCmd = new roseeCmd();
                $roseePRCmd->setName(__('Point de rosée', __FILE__));
                $roseePRCmd->setEqLogic_id($this->id);
                $roseePRCmd->setLogicalId('rosee');
                $roseePRCmd->setConfiguration('data', 'rosee_point');
                $roseePRCmd->setType('info');
                $roseePRCmd->setSubType('numeric');
                $roseePRCmd->setUnite('°C');
                $roseePRCmd->setIsHistorized(0);
                $roseePRCmd->setIsVisible(1);
                $roseePRCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseePRCmd->setOrder($order);
                $order ++;
                $roseePRCmd->save();
            }

		// Ajout d'une commande pour l'alerte givrage
            $roseeAGCmd = $this->getCmd(null, 'alerte_givre');
            if (!is_object($roseeAGCmd)) {
                $roseeAGCmd = new roseeCmd();
                $roseeAGCmd->setName(__('Alerte givre', __FILE__));
                $roseeAGCmd->setEqLogic_id($this->id);
                $roseeAGCmd->setLogicalId('alerte_givre');
                $roseeAGCmd->setConfiguration('data', 'alert_g');
                $roseeAGCmd->setType('info');
                $roseeAGCmd->setSubType('binary');
                $roseeAGCmd->setUnite('');
                $roseeAGCmd->setIsHistorized(0);
                $roseeAGCmd->setIsVisible(1);
                $roseeAGCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeAGCmd->setOrder($order);
                $order ++;
                $roseeAGCmd->save();
            }
        
		// Ajout d'une commande pour le point de givrage
            $roseePGCmd  = $this->getCmd(null, 'givrage');
            if (!is_object($roseePGCmd)) {
                $roseePGCmd = new roseeCmd();
                $roseePGCmd->setName(__('Point de givrage', __FILE__));
                $roseePGCmd->setEqLogic_id($this->id);
                $roseePGCmd->setLogicalId('givrage');
                $roseePGCmd->setConfiguration('data', 'frost_point');
                $roseePGCmd->setType('info');
                $roseePGCmd->setSubType('numeric');
                $roseePGCmd->setUnite('°C');
                $roseePGCmd->setIsHistorized(0);
                $roseePGCmd->setIsVisible(1);
                $roseePGCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseePGCmd->setOrder($order);
                $order ++;
                $roseePGCmd->save();
            }

        // Ajout d'une commande pour le message
            $roseeMGCmd = $this->getCmd(null, 'message_givre');
            if (!is_object($roseeMGCmd)) {
                $roseeMGCmd = new roseeCmd();
                $roseeMGCmd->setName(__('Message Alerte givre', __FILE__));
                $roseeMGCmd->setEqLogic_id($this->id);
                $roseeMGCmd->setLogicalId('message_givre');
                $roseeMGCmd->setConfiguration('data', 'message_givre');
                $roseeMGCmd->setUnite('');
                $roseeMGCmd->setType('info');
                $roseeMGCmd->setSubType('string');
                $roseeMGCmd->setIsHistorized(0);
                $roseeMGCmd->setIsVisible(0);
                $roseeMGCmd->setDisplay('generic_type','WEATHER_CONDITION');
                $roseeMGCmd->setOrder($order);
                $order ++;
                $roseeMGCmd->save();
            }
            
        // Ajout d'une commande pour la valeur numérique de l'alerte givre
            $roseeMNGCmd = $this->getCmd(null, 'message_givre_num');
            if (!is_object($roseeMNGCmd)) {
                $roseeMNGCmd = new roseeCmd();
                $roseeMNGCmd->setName(__('Message Alerte givre numérique', __FILE__));
                $roseeMNGCmd->setEqLogic_id($this->id);
                $roseeMNGCmd->setLogicalId('message_givre_num');
                $roseeMNGCmd->setConfiguration('data', 'message_givre_num');
                $roseeMNGCmd->setType('info');
                $roseeMNGCmd->setSubType('numeric');
                $roseeMNGCmd->setUnite('');
                $roseeMNGCmd->setIsHistorized(0);
                $roseeMNGCmd->setIsVisible(0);
                $roseeMNGCmd->setDisplay('generic_type','GENERIC_INFO');
                $roseeMNGCmd->setOrder($order);
                $order ++;
                $roseeMNGCmd->save();
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
            
            global $temperature,$OffsetT,$pression,$humidite,$dpr,$SHA,$calcul;
            
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
           
        //*  ********************** PRESSION *************************** */
            $pression = $this->getConfiguration('pression');
            if ($pression == '') {
                //valeur par défaut de la pression atmosphérique : 1013.25 hPa
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
            
            
        /*  ********************** Calcul de l'humidité absolue *************************** */
            log::add('rosee', 'debug', '┌───────── CALCUL DE L HUMIDITE ABSOLUE : '.$_eqName);
                if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
                    getHumidity();
        
                    // Résultat :
                        $humi_a_m3 = $GLOBALS["humi_a_m3"];
                        log::add('rosee', 'debug', '│ Humidité Absolue : ' . $humi_a_m3.' g/m3');
                        
                } else {
                    log::add('rosee', 'debug', '│ Pas de calcul de l\'Humidité Absolue');
                }
            log::add('rosee', 'debug', '└─────────');
            

        /*  ********************** Calcul du Point de rosée *************************** */
            log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE ROSEE : '.$_eqName);
                if ($calcul=='rosee_givre'|| $calcul=='rosee' ) {
                    getRosee();
                    
                    // Résultat :
                        $alert_r = $GLOBALS["alert_r"];
                            log::add('rosee', 'debug', '│ Etat alerte rosée : ' . $alert_r);
                        $rosee_point = $GLOBALS["rosee_point"];
                            log::add('rosee', 'debug', '│ Point de Rosée : ' . $rosee_point .' °C');
                } else {
                    $alert_r  = 0;
                    log::add('rosee', 'debug', '│ Pas de calcul du point de rosée et de l\'alerte  ');
                }
            log::add('rosee', 'debug', '└─────────');

            
        /*  ********************** Calcul du Point de givrage *************************** */
            log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE GIVRAGE : '.$_eqName);
                if ($calcul=='rosee_givre'|| $calcul=='givre' ) {
                    getGivre($temperature,$humi_a_m3,$SHA);
                    
                    // Résultat : 
                        $msg_givre_num = $GLOBALS["msg_givre_num"];
                        $msg_givre = $GLOBALS["msg_givre"];
                        $alert_g = $GLOBALS["alert_g"];
                        $msg_givre2 = $GLOBALS["msg_givre2"];
                        $msg_givre3 = $GLOBALS["msg_givre3"];
                        $frost_point = $GLOBALS["frost_point"];

                    log::add('rosee', 'debug', '│ ┌─────── CAS ACTUEL N°'.$msg_givre_num . ' / Alerte givre : ' .$alert_g );
                    log::add('rosee', 'debug', '│ │ Message : ' .$msg_givre );
                    log::add('rosee', 'debug', '│ │ Point de Givrage : ' . $frost_point.' °C');
                        if ($msg_givre2 != '' && msg_givre3 != ''){
                            log::add('rosee', 'debug', $msg_givre2 );
                            log::add('rosee', 'debug', $msg_givre3 );
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
            
            //Mise à jour de l'équipement Humidité absolue
                if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
                    $cmd = $this->getCmd('info', 'humidite_absolue');
                    if(is_object($cmd)) {
                        $cmd->setConfiguration('value', $humi_a_m3);
                        $cmd->save();
                        $cmd->event($humi_a_m3);
                            log::add('rosee', 'debug', '│ ┌───────── HUMIDITE ABSOLUE');
                            log::add('rosee', 'debug', '│ │ Humidité Absolue : ' . $humi_a_m3.' g/m3');
                            log::add('rosee', 'debug', '│ └─────────');
                    };
                }; 
              
            //Mise à jour de l'équipement Alerte rosée
                $cmd = $this->getCmd('info', 'alerte_rosee');
                if(is_object($cmd)) {
                    $cmd->setConfiguration('value', $alert_r);
                    $cmd->save();
                    $cmd->setCollectDate('');
                    $cmd->event($alert_r);
                    log::add('rosee', 'debug', '│ ┌───────── ROSEE');
                    log::add('rosee', 'debug', '│ │ Alerte Rosée : ' . $alert_r);
                };
        
            //Mise à jour de l'équipement point de rosée
                if ($calcul=='rosee_givre'|| $calcul=='rosee' ) {
                    $cmd = $this->getCmd('info', 'rosee');
                    $cmd->save();
                    if(is_object($cmd)) {
                        $cmd->setConfiguration('value', $rosee_point);
                        $cmd->save();
                        $cmd->event($rosee_point);
                            log::add('rosee', 'debug', '│ │ Point de Rosée : ' . $rosee_point.' °C');
                    };
                };
                            log::add('rosee', 'debug', '│ └─────────');

            if ($calcul=='rosee_givre'|| $calcul=='givre' ) {
                //Mise à jour de l'équipement Alerte givre
                    $cmd = $this->getCmd('info', 'alerte_givre');
                    if (is_object($cmd)) {
                        $cmd->setConfiguration('value', $alert_g);
                        $cmd->save();
                        $cmd->setCollectDate('');
                        $cmd->event($alert_g);
                            log::add('rosee', 'debug', '│ ┌───────── GIVRE');
                            log::add('rosee', 'debug', '│ │ Alerte Givre : ' . $alert_g);
                    };

                //Mise à jour de l'équipement Givrage
                    $cmd = $this->getCmd('info', 'givrage');
                    if(is_object($cmd)) {
                        $cmd->setConfiguration('value', $frost_point);
                        $cmd->save();
                        $cmd->event($frost_point);
                            log::add('rosee', 'debug', '│ │ Point de givrage : ' . $frost_point.' °C');
                    }

                //Mise à jour de l'équipement message
                    $cmd = $this->getCmd('info', 'message_givre');
                    if(is_object($cmd)) {
                        $cmd->setConfiguration('value', $msg_givre);
                        $cmd->save();
                        $cmd->setCollectDate('');
                        $cmd->event($msg_givre);
                            log::add('rosee', 'debug', '│ │ Message Alerte givre : ' . $msg_givre);
                    }

                //Mise à jour de l'équipement message
                    $cmd = $this->getCmd('info', 'message_givre_num');
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
                        log::add('rosee', 'debug', '│ ┌───────── GIVRE');
                        log::add('rosee', 'debug', '│ │ Suppression : ' );
                   // };
                 };
                log::add('rosee', 'debug', '└─────────');
                log::add('rosee', 'debug', '================ FIN CRON =================');
            return;
        }
}

    
function getHumidity() {
    /*  ********************** Calcul de l'humidité absolue *************************** */

        $terme_pvs1 = 2.7877 + (7.625 * $GLOBALS["temperature"]) / (241.6 + $GLOBALS["temperature"]);
        $pvs = pow(10,$terme_pvs1);                                             // pression de saturation de la vapeur d'eau
        $pv = ($GLOBALS["humidite"] * $pvs) / 100.0;                                       // pression partielle de vapeur d'eau
        $GLOBALS["pression"] = $GLOBALS["pression"] * 100.0;                                          // conversion de la pression en Pa
        $humi_a = 0.622 * ($pv / ($GLOBALS["pression"] - $pv));                            // Humidité absolue en kg d'eau par kg d'air
        $v = (461.24 * (0.622 + $humi_a) * ($GLOBALS["temperature"]+273.15)) / $GLOBALS["pression"];  // Volume specifique en m3 / kg
        $p = 1.0 / $v;                                                          // Poids spécifique en kg / m3
        global $humi_a_m3; 
            $humi_a_m3 = 1000.0 * $humi_a * $p;                                 // Humidité absolue en gr / m3
            $humi_a_m3 = round(($humi_a_m3), 1);                                // Humidité absolue en gr / m3 (1 chiffre après la virgule)     
            
        log::add('rosee', 'debug', '│ terme_pvs1 : ' . $terme_pvs1);
        log::add('rosee', 'debug', '│ pvs : ' . $pvs);
        log::add('rosee', 'debug', '│ pv : ' . $pv);
        log::add('rosee', 'debug', '│ Pression : ' . $pression.' Pa');
        log::add('rosee', 'debug', '│ humi_a : ' . $humi_a);
        log::add('rosee', 'debug', '│ v : ' . $v);
        log::add('rosee', 'debug', '│ p : ' . $p);

    return;
}

function getRosee () {
    /*  ********************** Calcul du Point de rosée ***************************
        paramètres de MAGNUS pour l'air saturé (entre -45°C et +60°C) :
            alpha  = 6.112 hPa
            beta   = 17.62
            lambda = 243.12 °C*/
        global $alert_r,$rosee_point;         
        $alpha = 6.112;
            log::add('rosee', 'debug', '│ alpha : ' . $alpha );
    
        $beta = 17.62;
            log::add('rosee', 'debug', '│ beta : ' . $beta );
    
        $lambda = 243.12;
            log::add('rosee', 'debug', '│ Lambda : ' . $lambda );
    
        $Terme1 = log($GLOBALS["humidite"]/100);
            log::add('rosee', 'debug', '│ Terme1 : ' . $Terme1 );
    
        $Terme2 = ($beta * $GLOBALS["temperature"]) / ($lambda + $GLOBALS["temperature"]);
            log::add('rosee', 'debug', '│ Terme2 : ' . $Terme2 );
    
        $rosee = $lambda * ($Terme1 + $Terme2) / ($beta - $Terme1 - $Terme2);
            log::add('rosee', 'debug', '│ rosee : ' . $rosee .' °C');
    
        $rosee_point = round(($rosee), 1);
            
    /*  ********************** Calcul de l'alerte rosée en fonction du seuil d'alerte *************************** */
        $frost_alert_rosee = $GLOBALS["temperature"] - $rosee_point;
        
            log::add('rosee', 'debug', '│ Calcul point de rosée : (Température - point de Rosée) : (' .$GLOBALS["temperature"] .' - '.$rosee_point .' )= ' . $frost_alert_rosee );
            if ($frost_alert_rosee <= $GLOBALS["dpr"]) {
                $alert_r = 1;
                log::add('rosee', 'debug', '│ Résultat : Calcul point de rosée (Calcul point de Rosée  <= Seuil DPR) = (' .$frost_alert_rosee .' <= ' .$GLOBALS["dpr"] .')');
            } else {
                $alert_r = 0;
                log::add('rosee', 'debug', '│ Résultat : Calcul point de rosée (Calcul point de Rosée  > Seuil DPR)= (' .$frost_alert_rosee .' > ' .$GLOBALS["dpr"] .')');
            }
    
    return;
}
function getGivre ($temperature,$humi_a_m3,$SHA) {
    /*  ********************** Calcul du Point de givrage *************************** */
        global $msg_givre, $msg_givre_num, $alert_g,$frost_point,$msg_givre2,$msg_givre3;
            //$GLOBALS["SHA"]
        if ($GLOBALS["temperature"] <= 5  ) {
            $msg_givre2 ='';
            $msg_givre3 ='';

            $temp_kelvin = $GLOBALS["temperature"] + 273.15;
            $rosee_kelvin = $rosee + 273.15;
            $frost_kelvin = 2954.61 / $temp_kelvin;
            $frost_kelvin = $frost_kelvin + 2.193665 * log($temp_kelvin);
            $frost_kelvin = $frost_kelvin - 13.3448;
            $frost_kelvin = 2671.02 / $frost_kelvin;
            $frost_kelvin = $frost_kelvin + $rosee_kelvin - $temp_kelvin;
            $frost = $frost_kelvin -273.15;
            $frost_point = round(($frost), 1);
                
                // Déclaration des variables
                    // Cas N°0
                        $msg_givre_0 = 'Aucun risque de Givre';
                        $msg_givre_num_0 = 0;
                        $alert_g_0 = 0;
                    // Cas N°1
                        $msg_givre_1 = 'Givre peu probable malgré la température';
                        $msg_givre_num_1 = 1;
                        $alert_g_1 = 1;
                    // Cas N°2
                        $msg_givre_2 = 'Risque de givre';
                        $msg_givre_num_2 = 2;
                        $alert_g_2 = 1;
                    // Cas N°3
                        $msg_givre_3 = 'Givre, Présence de givre';
                        $msg_givre_num_3 = 3;
                        $alert_g_3 = 1;

                // Cas Actuel
                        if($GLOBALS["temperature"] <= 1 && $frost_point <= 0) {
                            if ($GLOBALS["humi_a_m3"] > $GLOBALS["SHA"]) {
                                // Cas N°3
                                $msg_givre = $msg_givre_3;
                                $msg_givre_num = $msg_givre_num_3;
                                $alert_g  = $alert_g_3;
                                $alert_r = 0;
                            };
                            if ($GLOBALS["humi_a_m3"] < $$GLOBALS["SHA"]) {
                                // Cas N°1
                                $msg_givre = $msg_givre_1;
                                $msg_givre_num = $msg_givre_num_1;
                                $alert_g  = $alert_g_1;
                                $alert_r = 0;
                            };
                        } elseif ($GLOBALS["temperature"] <= 4 && $frost_point <= 0.5) {
                                // Cas N°2
                                $msg_givre = $msg_givre_2;
                                $msg_givre_num = $msg_givre_num_2;
                                $alert_g  = $alert_g_2;
                                $alert_r = 0;
                        } else {
                                // Cas N°0
                                $msg_givre = $msg_givre_0;
                                $msg_givre_num = $msg_givre_num_0;
                                $alert_g  = $alert_g_0;
                        };
        } else {
            $msg_givre = 'Aucun risque de Givre';
            $msg_givre_num = 0;
            $alert_g  = 0;
            $frost_point = 5;
            $msg_givre2 ='│ │ Info supplémentaire : Il fait trop chaud pas de calcul de l\'alerte givre';
            $msg_givre3 ='│ │ Info supplémentaire : Point de givre fixé est : ' .$frost_point .' °C';
        };

    return;
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
?>