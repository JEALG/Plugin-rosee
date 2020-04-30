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
    public static function cron5($_eqlogic_id = null) {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 5 ==================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron10($_eqlogic_id = null) {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 10 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron15($_eqlogic_id = null) {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 15 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cron30() {
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON 30 =================');
                $rosee->getInformations();
            }
        }
    }

    public static function cronHourly($_eqlogic_id = null) {
        //no both cron30 and cronHourly enabled:
        if (config::byKey('functionality::cron30::enable', 'rosee', 0) == 1) {
            config::save('functionality::cronHourly::enable', 0, 'rosee');
            return;
        }
        foreach (eqLogic::byType('rosee') as $rosee) {
            if ($rosee->getIsEnable()) {
                log::add('rosee', 'debug', '================= CRON HEURE =================');
                $rosee->getInformations();
            }
        }
    }
    // Template pour la tendance
    public static function templateWidget(){
        $return = array('info' => array('numeric' => array()));
        $return['info']['numeric']['tendance'] = array(
            'template' => 'tmplmultistate',
            'test' => array(
                array('operation' => '#value# == 0','state_light' => '<img src=plugins/rosee/core/template/img/tendance_0.png>'),
                array('operation' => '#value# == 1','state_light' => '<img src=plugins/rosee/core/template/img/tendance_1.png>'),
                array('operation' => '#value# == 2','state_light' => '<img src=plugins/rosee/core/template/img/tendance_2.png>'),
                array('operation' => '#value# == 3','state_light' => '<img src=plugins/rosee/core/template/img/tendance_3.png>'),
                array('operation' => '#value# == 4','state_light' => '<img src=plugins/rosee/core/template/img/tendance_4.png>'),
                array('operation' => '#value# == 5','state_light' => '<img src=plugins/rosee/core/template/img/tendance_5.png>')
            )
        );
        return $return;
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

        if ($this->getConfiguration('type_calcul') == '') {
            throw new Exception(__('Le champ "Calcul" ne peut être vide',__FILE__));
            log::add('rosee', 'error', '│ Configuration : Méthode de Calcul inexistant : ' . $this->getConfiguration('type_calcul'));
        }
    }
    public function getImage() {
    if($this->getConfiguration('type_calcul') != ''){
      $filename = 'plugins/rosee/core/config/img/' . $this->getConfiguration('type_calcul').'.png';
      if(file_exists(__DIR__.'/../../../../'.$filename)){
        return $filename;
      }
    }
    return 'plugins/rosee/plugin_info/rosee_icon.png';
  }

    public function postInsert() {

    }

    public function postSave(){
        log::add('rosee', 'debug', 'postSave()');
        $order = 1;
        /*  ********************** Calcul *************************** */
        $calcul=$this->getConfiguration('type_calcul');
        if ($calcul=='tendance') {
            $td_num_max =5;
            $td_num_visible =1;
        }else{
            $td_num_max =3;
            $td_num_visible =0;
        }

        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            $roseeCmd = $this->getCmd(null, 'humidityabs');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Humidité absolue', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('humidityabs');
                $roseeCmd->setConfiguration('data', 'humidityabs');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','WEATHER_HUMIDITY');
                $roseeCmd->setTemplate('dashboard','core::line');
                $roseeCmd->setTemplate('mobile','core::line');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('g/m3');
            $roseeCmd->setLogicalId('humidityabs');
            $roseeCmd->setType('info');
            $roseeCmd->setSubType('numeric');
            $roseeCmd->save();
        }

        if ($calcul=='rosee_givre'|| $calcul=='rosee') {
            $roseeCmd = $this->getCmd(null, 'alert_1');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Alerte rosée', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('alert_1');
                $roseeCmd->setConfiguration('data', 'alert_1');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('binary');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeCmd->setTemplate('dashboard','core::line');
                $roseeCmd->setTemplate('mobile','core::line');
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
                $roseeCmd->setTemplate('dashboard','core::line');
                $roseeCmd->setTemplate('mobile','core::line');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('°C');
            $roseeCmd->save();
        }

        if ($calcul=='rosee_givre'|| $calcul=='givre') {
            $roseeCmd = $this->getCmd(null, 'alert_2');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Alerte givre', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('alert_2');
                $roseeCmd->setConfiguration('data', 'alert_2');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('binary');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible(1);
                $roseeCmd->setDisplay('generic_type','SIREN_STATE');
                $roseeCmd->setTemplate('dashboard','core::line');
                $roseeCmd->setTemplate('mobile','core::line');
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
                $roseeCmd->setTemplate('dashboard','core::line');
                $roseeCmd->setTemplate('mobile','core::line');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('°C');
            $roseeCmd->save();
        }
        if ($calcul == 'tendance'|| $calcul=='rosee_givre'|| $calcul=='givre') {
            $roseeCmd = $this->getCmd(null, 'td');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Message', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('td');
                $roseeCmd->setConfiguration('data', 'td');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('string');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible($td_num_visible);
                $roseeCmd->setDisplay('generic_type','WEATHER_CONDITION');
                $roseeCmd->setTemplate('dashboard','core::multiline');
                $roseeCmd->setTemplate('mobile','core::multiline');
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->save();

            $roseeCmd = $this->getCmd(null, 'td_num');
            if (!is_object($roseeCmd)) {
                $roseeCmd = new roseeCmd();
                $roseeCmd->setName(__('Message numérique', __FILE__));
                $roseeCmd->setEqLogic_id($this->id);
                $roseeCmd->setLogicalId('td_num');
                $roseeCmd->setConfiguration('data', 'td_num');
                $roseeCmd->setType('info');
                $roseeCmd->setSubType('numeric');
                $roseeCmd->setIsHistorized(0);
                $roseeCmd->setIsVisible($td_num_visible);
                $roseeCmd->setDisplay('generic_type','GENERIC_INFO');
                if ($calcul=='tendance') {
                    $roseeCmd->setTemplate('dashboard','rosee::tendance');
                    $roseeCmd->setTemplate('mobile','rosee::tendance');
                } else {
                    $roseeCmd->setTemplate('dashboard','core::line');
                    $roseeCmd->setTemplate('mobile','core::line');
                }
                $roseeCmd->setOrder($order);
                $order ++;
            }
            $roseeCmd->setEqLogic_id($this->getId());
            $roseeCmd->setUnite('');
            $roseeCmd->setConfiguration('minValue', 0);
            $roseeCmd->setConfiguration('maxValue', $td_num_max);
            $roseeCmd->save();
        }

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new roseeCmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
            $refresh->setOrder($order);
        }
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->setEqLogic_id($this->getId());
        $refresh->save();
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

        /*  ********************** Calcul *************************** */
        $calcul=$this->getConfiguration('type_calcul');
        if ($calcul== '') {
            throw new Exception(__('Le champ "Calcul" ne peut être vide',__FILE__));
            log::add('rosee', 'error', '│ Configuration : Méthode de Calcul inexistant : ' . $this->getConfiguration('type_calcul'));
        }
        log::add('rosee', 'debug', '│ Méthode de calcul : ' . $calcul);

        /*  ********************** TEMPERATURE *************************** */
        $idvirt = str_replace("#","",$this->getConfiguration('temperature'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $temperature = $cmdvirt->execCmd();
            log::add('rosee', 'debug', '│ Température : ' . $temperature.' °C');
        } else {
            if ($calcul != 'tendance') {
                throw new Exception(__('Le champ "Température" ne peut être vide',__FILE__));
                log::add('rosee', 'error', '│ Configuration : Température inexistante : ' . $this->getConfiguration('temperature'));
            }
        }

        /*  ********************** Offset Température *************************** */
        if ($calcul != 'tendance') {
            $OffsetT=$this->getConfiguration('OffsetT');
            if ($OffsetT== '') {
                $OffsetT=0;
                log::add('rosee', 'debug', '│ Aucun Offset Température : ' . $OffsetT.'');
            } else {
                log::add('rosee', 'debug', '│ Offset Température : ' . $OffsetT.'');
                $temperature = $temperature + $OffsetT;
                log::add('rosee', 'debug', '│ Température avec Offset : ' .$temperature.' °C');
            }
        }

        /*  ********************** PRESSION *************************** */
        $pressure = $this->getConfiguration('pression');
        if ($pressure == '' && $calcul !='tendance') {//valeur par défaut de la pression atmosphérique : 1013.25 hPa
            $pressure=1013.25;
            log::add('rosee', 'debug', '│ Pression Atmosphérique aucun équipement sélectionné, valeur par défaut : '. $pressure. ' hPa');
        } else {
            $pressureID = str_replace("#","",$this->getConfiguration('pression'));
            $cmdvirt = cmd::byId($pressureID);
            if (is_object($cmdvirt)) {
                $pressure = $cmdvirt->execCmd();
                log::add('rosee', 'debug', '│ Pression Atmosphérique : ' . $pressure.' hPa');
            } else {
                throw new Exception(__('Le champ "Pression Atmosphérique" ne peut être vide',__FILE__));
                log::add('rosee', 'error', '│ Configuration : Pression Atmosphérique inexistante : ' . $this->getConfiguration('pression'));
            }
        }

        /*  ********************** HUMIDITE *************************** */
        $idvirt = str_replace("#","",$this->getConfiguration('humidite'));
        $cmdvirt = cmd::byId($idvirt);
        if (is_object($cmdvirt)) {
            $humidity = $cmdvirt->execCmd();
            log::add('rosee', 'debug', '│ Humidité Relative : ' . $humidity.' %');
        } else {
            if ($calcul != 'tendance') {
                throw new Exception(__('Le champ "Humidité Relative" ne peut être vide',__FILE__));
                log::add('rosee', 'error', '│ Configuration : Humidité Relative  inexistante : ' . $this->getConfiguration('humidite'));
            }
        }


        /*  ********************** SEUIL D'ALERTE ROSEE *************************** */
        if ($calcul == 'rosee' || $calcul == 'rosee_givre'  ) {
            $dpr=$this->getConfiguration('DPR');
            if ($dpr == '') {
                $dpr=2.0;
                log::add('rosee', 'debug', '│ Seuil DPR : Aucune valeur de saisie => Valeur par défaut : '. $dpr.' °C');
            } else {
                log::add('rosee', 'debug', '│ Seuil DPR : ' . $dpr.' °C');
            }
        }

        /*  ********************** SEUIL D'HUMIDITE ABSOLUE ***************************  */
        if ($calcul == 'givre' || $calcul == 'rosee_givre'  ) {
            $SHA=$this->getConfiguration('SHA');
            if ($SHA == '') {
                $SHA=2.8;
                log::add('rosee', 'debug', '│ Seuil d\'Humidité Absolue : Aucune valeur de saisie => Valeur par défaut : ' . $SHA.'');
            } else {
                log::add('rosee', 'debug', '│ Seuil d\'Humidité Absolue : ' . $SHA.'');
            }
        }
        log::add('rosee', 'debug', '└─────────');

        /*  ********************** Conversion (si Besoin) *************************** */

        /*  ********************** Calcul de l'humidité absolue *************************** */
        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            log::add('rosee', 'debug', '┌───────── CALCUL DE L\'HUMIDITE ABSOLUE : '.$_eqName);
            $humidityabs_m3 = rosee::getHumidity($temperature, $humidity,$pressure);
            log::add('rosee', 'debug', '│ Humidité Absolue : ' . $humidityabs_m3.' g/m3');
            log::add('rosee', 'debug', '└─────────');
        }

        /*  ********************** Calcul de la tendance *************************** */
        if ($calcul=='tendance') {
            log::add('rosee', 'debug', '┌───────── CALCUL DE LA TENDANCE : '.$_eqName);
            $va_result_T = rosee::getTendance($pressureID);
            // Partage des données du tableau
            $td_num = $va_result_T [0];
            $td = $va_result_T [1];
            //log::add('rosee', 'debug' , '│ Tendance : ' . $td . '' );
            //log::add('rosee', 'debug' , '│ Tendance numérique : ' . $td_num . '');
            log::add('rosee', 'debug' , '└─────────');
        }

        /*  ********************** Calcul du Point de rosée *************************** */
        $alert_1  = 0;
        if ($calcul=='rosee_givre'|| $calcul=='rosee' || $calcul=='givre' ) {
            log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE ROSEE : '.$_eqName);
            $va_result_R = rosee::getRosee($temperature, $humidity, $dpr);
            // Partage des données du tableau
            $rosee_point = $va_result_R [0];
            $alert_1 = $va_result_R [1];
            $rosee = $va_result_R [2];

            if ($calcul=='rosee_givre'|| $calcul=='rosee') {
                log::add('rosee', 'debug', '│ Etat alerte rosée : ' . $alert_1);
                log::add('rosee', 'debug', '│ Point de Rosée : ' . $rosee_point .' °C');
            } else {
                log::add('rosee', 'debug', '│ Pas de mise à jour du point de  l\'alerte rosée car le calcul est désactivé');
            }
            log::add('rosee', 'debug', '└─────────');
        }

        /*  ********************** Calcul du Point de givrage *************************** */
        if ($calcul=='rosee_givre'|| $calcul=='givre' ) {
            log::add('rosee', 'debug', '┌───────── CALCUL DU POINT DE GIVRAGE : '.$_eqName);
            $va_result_G = rosee::getGivre($temperature, $SHA, $humidityabs_m3, $rosee);
            // Partage des données du tableau
            $td_num = $va_result_G [0];
            $td = $va_result_G [1];
            $alert_2  = $va_result_G [2];
            $frost_point  = $va_result_G [3];
            $msg_givre2 = $va_result_G [4];
            $msg_givre3 = $va_result_G [5];

            log::add('rosee', 'debug', '│ Cas Actuel N°' .$td_num );
            log::add('rosee', 'debug', '│ Alerte givre : ' .$alert_2);
            log::add('rosee', 'debug', '│ Message : ' .$td );
            log::add('rosee', 'debug', '│ Point de Givrage : ' . $frost_point.' °C');
            if ($msg_givre2 != '' && $msg_givre3 != ''){
                log::add('rosee', 'debug', $msg_givre2 );
                log::add('rosee', 'debug', $msg_givre3 );
            };
            if ($alert_2 == 1 && $alert_1 == 1) {
                $alert_1 = 0;
                log::add('rosee', 'debug', '│ Annulation alerte rosée : ' .$alert_1 );
            };
            log::add('rosee', 'debug', '└───────');
        } else {
            $alert_2 = 0;
            $frost_point = 5;
            //log::add('rosee', 'debug', '│ Pas de calcul du point de givrage et de l\'alerte');
        };

        /*  ********************** Mise à Jour des équipements *************************** */
        log::add('rosee', 'debug', '┌───────── MISE A JOUR : '.$_eqName);

        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='humidityabs') {
            $cmd = $this->getCmd('info', 'humidityabs');//Mise à jour de l'équipement Humidité absolue
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $humidityabs_m3);
                $cmd->save();
                $cmd->event($humidityabs_m3);
                log::add('rosee', 'debug', '│ ┌───────── HUMIDITE ABSOLUE');
                log::add('rosee', 'debug', '│ │ Humidité Absolue : ' . $humidityabs_m3.' g/m3');
                log::add('rosee', 'debug', '│ └─────────');
            };
        };

        if ($calcul=='rosee_givre'|| $calcul=='rosee'){
            $cmd = $this->getCmd('info', 'alert_1');//Mise à jour de l'équipement Alerte rosée
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $alert_1);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($alert_1);
                log::add('rosee', 'debug', '│ ┌───────── ROSEE');
                log::add('rosee', 'debug', '│ │ Etat Alerte Rosée : ' . $alert_1);
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
            $cmd = $this->getCmd('info', 'alert_2');//Mise à jour de l'équipement Alerte givre
            if (is_object($cmd)) {
                $cmd->setConfiguration('value', $alert_2);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($alert_2);
                log::add('rosee', 'debug', '│ ┌───────── GIVRE');
                log::add('rosee', 'debug', '│ │ Etat Alerte Givre : ' . $alert_2);
            };

            $cmd = $this->getCmd('info', 'givrage');//Mise à jour de l'équipement Givrage
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $frost_point);
                $cmd->save();
                $cmd->event($frost_point);
                log::add('rosee', 'debug', '│ │ Point de givrage : ' . $frost_point.' °C');
            }
        }

        if ($calcul=='tendance') {
            $start_log_td =' │ Tendance : ';
            $start_log_td_num =' │ Tendance numérique : ';
            log::add('rosee', 'debug', '│ ┌───────── MESSAGE');
        }else{
            $start_log_td =' │ Message Alerte givre : ';
            $start_log_td_num =' │ Message Alerte givre numérique : ';
        }

        if ($calcul=='rosee_givre'|| $calcul=='givre' || $calcul=='tendance' ) {
            $cmd = $this->getCmd('info', 'td'); //Mise à jour de l'équipement message
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $td);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($td);
                log::add('rosee', 'debug', '│'.$start_log_td . $td);
            }

            $cmd = $this->getCmd('info', 'td_num'); //Mise à jour de l'équipement message
            if(is_object($cmd)) {
                $cmd->setConfiguration('value', $td_num);
                $cmd->save();
                $cmd->setCollectDate('');
                $cmd->event($td_num);
                log::add('rosee', 'debug', '│'.$start_log_td_num . $td_num);
            };
            log::add('rosee', 'debug','│ └─────────');
        } else{
            // $cmd = $this->getCmd('info', 'alert_2');
            //if (is_object($cmd)) {
            //  $cmd->setConfiguration('value', $alert_2);
            //$cmd->remove();
            //$cmd->save();
            // };
        };

        log::add('rosee', 'debug', '└─────────');
        log::add('rosee', 'debug', '================ FIN CRON =================');
        return;
    }
    /*  ********************** Calcul de l'humidité absolue *************************** */
    public function getHumidity($temperature, $humidity, $pressure) {
        $terme_pvs1 = 2.7877 + (7.625 * $temperature) / (241.6 + $temperature);
        log::add('rosee', 'debug', '│ terme_pvs1 : ' . $terme_pvs1);
        $pvs = pow(10,$terme_pvs1);
        log::add('rosee', 'debug', '│ Pression de saturation de la vapeur d\'eau (pvs) : ' . $pvs);
        $pv = ($humidity * $pvs) / 100.0;
        log::add('rosee', 'debug', '│ Pression partielle de vapeur d\'eau (pv) : ' . $pv);
        $humi_a = 0.622 * ($pv / (($pressure * 100.0) - $pv));
        log::add('rosee', 'debug', '│ Humidité absolue en kg d\'eau par kg d\'air : ' . $humi_a .' kg');
        $v = (461.24 * (0.622 + $humi_a) * ($temperature +273.15)) / ($pressure * 100.0);
        log::add('rosee', 'debug', '│ Volume specifique (v) : ' . $v .' m3/kg');
        $p = 1.0 / $v;
        log::add('rosee', 'debug', '│ Poids spécifique (p) : ' . $p.' m3/kg');
        $humidityabs_m3 = 1000.0 * $humi_a * $p;
        $humidityabs_m3 = round(($humidityabs_m3), 1);
        return $humidityabs_m3;
    }

    /*  ********************** Calcul du Point de rosée *************************** */
    public function getRosee($temperature, $humidity, $dpr) {
        /* Paramètres de MAGNUS pour l'air saturé (entre -45°C et +60°C) : */
        $alpha = 6.112;
        $beta = 17.62;
        $lambda = 243.12;
        log::add('rosee', 'debug', '│ Paramètres de MAGNUS pour l\'air saturé (entre -45°C et +60°C) : Lambda = ' . $lambda .' °C // alpha = ' . $alpha .' hPa // beta = ' . $beta );

        $Terme1 = log($humidity/100);
        $Terme2 = ($beta * $temperature) / ($lambda + $temperature);
        log::add('rosee', 'debug', '│ Terme1 = ' . $Terme1 .' // Terme2 = ' . $Terme2 );
        $rosee = $lambda * ($Terme1 + $Terme2) / ($beta - $Terme1 - $Terme2);
        $rosee_point = round(($rosee), 1);
        $alert_1 = 0;

        /*  ********************** Calcul de l'alerte rosée en fonction du seuil d'alerte *************************** */
        $frost_alert_rosee = $temperature - $rosee_point;
        log::add('rosee', 'debug', '│ Calcul point de rosée : (Température - point de Rosée) : (' .$temperature .' - '.$rosee_point .' )= ' . $frost_alert_rosee .' °C');
        if ($frost_alert_rosee <= $dpr) {
            $alert_1 = 1;
            log::add('rosee', 'debug', '│ Résultat : Calcul Alerte point de rosée = (' .$frost_alert_rosee .' <= ' .$dpr .') = Alerte active');
        } else {
            log::add('rosee', 'debug', '│ Résultat : Calcul Alerte point de rosée = (' .$frost_alert_rosee .' > ' .$dpr .') = Alerte désactivée');
        }

        return array($rosee_point, $alert_1,$rosee);
    }
    /*  ********************** Calcul du Point de givrage *************************** */
    public function getGivre($temperature, $SHA, $humidityabs_m3, $rosee) {
        $td = 'Aucun risque de Givre';
        $td_num = 0;
        $alert_2  = 0;
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
                $alert_2  = 1;
                if ($humidityabs_m3 > $SHA) {// Cas N°3
                    $td = 'Givre, Présence de givre';
                    $td_num = 3;
                };
                if ($humidityabs_m3 < $SHA) {// Cas N°1
                    $td = 'Givre peu probable malgré la température';
                    $td_num = 1;
                };
            } elseif ($temperature <= 4 && $frost_point <= 0.5) {// Cas N°2
                $td = 'Risque de givre';
                $td_num = 2;
                $alert_2  = 1;
            //} else {// Cas N°0
            };
        } else {
            $frost_point = 5;
            $msg_givre2 ='│ Info supplémentaire : Il fait trop chaud pas de calcul de l\'alerte givre (' .$temperature .' °C > 5 °C)';
            $msg_givre3 ='│ Info supplémentaire : Point de givre fixé est : ' .$frost_point .' °C';
        };
        return array ($td_num, $td, $alert_2, $frost_point,$msg_givre2 ,$msg_givre3);
    }
    /*  ********************** Calcul de la tendance *************************** */
    public function getTendance($pressureID) {
        $histo = new scenarioExpression();
        $endDate = $histo -> collectDate($pressureID);

        // calcul du timestamp actuel
        log::add('rosee', 'debug', '│ ┌─────── Timestamp -15min');
        $_date1 = new DateTime("$endDate");
        $_date2 = new DateTime("$endDate");
        $startDate = $_date1 -> modify('-15 minute');
        $startDate = $_date1 -> format('Y-m-d H:i:s');
        log::add('rosee', 'debug', '│ │ Start Date : ' .$startDate );
        log::add('rosee', 'debug', '│ │ End Date : ' .$endDate );

        // dernière mesure barométrique
        $h1 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add('rosee', 'debug', '│ │ Pression Atmosphérique : ' .$h1 . ' hPa' );
        log::add('rosee', 'debug', '│ └───────');

        // calcul du timestamp - 2h
        log::add('rosee', 'debug', '│ ┌─────── Timestamp -2h');
        $endDate = $_date2 -> modify('-2 hour');
        $endDate = $_date2 -> format('Y-m-d H:i:s');
        $startDate = $_date1 -> modify('-2 hour');
        $startDate = $_date1 -> format('Y-m-d H:i:s');
        log::add('rosee', 'debug', '│ │ Start Date : ' .$startDate );
        log::add('rosee', 'debug', '│ │ End Date : ' .$endDate );

        // mesure barométrique -2h
        $h2 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add('rosee', 'debug', '│ │ Pression Atmosphérique : ' .$h2 . ' hPa' );

        // calculs de tendance 15min/2h
        $td2h = ($h1 - $h2) / 2;
        log::add('rosee', 'debug', '│ │ Tendance : ' . $td2h . ' hPa/h' );
        log::add('rosee', 'debug', '│ └───────');

        // calcul du timestamp - 4h
        log::add('rosee', 'debug', '│ ┌─────── Timestamp -4h');
        $endDate = $_date2 -> modify('-2 hour');
        $endDate = $_date2 -> format('Y-m-d H:i:s');
        $startDate = $_date1 -> modify('-2 hour');
        $startDate = $_date1 -> format('Y-m-d H:i:s');
        log::add('rosee', 'debug', '│ │ Start Date : ' .$startDate );
        log::add('rosee', 'debug', '│ │ End Date : ' .$endDate );

        // mesure barométrique -4h
        $h4 = $histo->lastBetween($pressureID, $startDate, $endDate);
        log::add('rosee', 'debug', '│ │ Pression Atmosphérique : ' .$h4 . ' hPa' );

        // calculs de tendance 2h/4h
        $td4h = ($h1 - $h4) / 4;
        log::add('rosee', 'debug', '│ │ Tendance : ' . $td4h . ' hPa/h' );
        log::add('rosee', 'debug', '│ └───────');


        // calculs de tendance
        log::add('rosee', 'debug', '│ ┌───────── Calcul Tendance Moyenne');
        // sources : http://www.freescale.com/files/sensors/doc/app_note/AN3914.pdf
        // et : https://www.parallax.com/sites/default/files/downloads/29124-Altimeter-Application-Note-501.pdf

        // moyennation de la tendance à -2h (50%) et -4h (50%)
        $td_moy = (0.5 * $td2h + 0.5 * $td4h);
        $dPdT = number_format($td_moy, 3, '.', '');
        log::add('rosee', 'debug', '│ │ Tendance Moyenne (dPdT): ' . $dPdT . ' hPa/h' );

        if ($td_moy > 2.5) { // Quickly rising High Pressure System, not stable
            $td = 'Forte embellie, instable';
            $td_num=5;
        } elseif ($td_moy > 0.5 && $td_moy <= 2.5) { // Slowly rising High Pressure System, stable good weather
            $td='Amélioration, beau temps durable';
            $td_num=4;
        } elseif ($td_moy > 0.0 && $td_moy <= 0.5) { // Stable weather condition
            $td='Lente amélioration, temps stable';
            $td_num=3;
        } elseif ($td_moy > -0.5 && $td_moy <= 0) { // Stable weather condition
            $td='Lente dégradation, temps stable';
            $td_num=2;
        } elseif ($td_moy > -2.5 && $td_moy <= -0.5) { // Slowly falling Low Pressure System, stable rainy weather
            $td='Dégradation, mauvais temps durable';
            $td_num=1;
        } else { // Quickly falling Low Pressure, Thunderstorm, not stable
            $td='Forte dégradation, instable';
            $td_num=0;
        };
        log::add('rosee', 'debug', '│ └─────────');
        return array ($td_num, $td);
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
