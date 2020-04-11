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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function rosee_install() {
    jeedom::getApiKey('rosee');
    config::save('functionality::cron5::enable', 1, 'rosee');
    config::save('functionality::cron30::enable', 0, 'rosee');
    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
    message::add('rosee', 'Installation du plugin Rosée terminée.');
}

function rosee_update() {
    jeedom::getApiKey('rosee');
    log::add('rosee', 'debug', '┌───────── Mise à jour Plugin');

    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
    log::add('rosee', 'debug', '│ Suppression Cron');
    if (config::byKey('functionality::cron5::enable', 'rosee', -1) == -1)
        config::save('functionality::cron5::enable', 1, 'rosee');

    log::add('rosee', 'debug', '│ Cron5');

    if (config::byKey('functionality::cron30::enable', 'rosee', -1) == -1)
        config::save('functionality::cron30::enable', 0, 'rosee');
    log::add('rosee', 'debug', '│ Cron30');

    $plugin = plugin::byId('rosee');
    $eqLogics = eqLogic::byType($plugin->getId());
    foreach ($eqLogics as $eqLogic) {
        updatename($eqLogic, 'message_givre', 'td');
        updatename($eqLogic, 'message_givre_num', 'td_num');
        //$eqLogics->save();
    }
    log::add('rosee', 'debug', '│ TEST');
    message::add('rosee', 'Mise à jour du plugin Rosée terminée.');
    //resave eqLogics for new cmd:
    try
    {
        $eqs = eqLogic::byType('rosee');
        foreach ($eqs as $eq)
        {
            $eq->save();
        }
    }
    catch (Exception $e)
    {
        $e = print_r($e, 1);
        log::add('rosee', 'error', 'rosee_update ERROR: '.$e);
    }
    log::add('rosee', 'debug', '└─────────');

}

function updateLogicalId($eqLogic, $from, $to) {
    //  Fonction pour renommer une commande
    $roseeCmd = $eqLogic->getCmd(null, $from);
    if (is_object($roseeCmd)) {
        $roseeCmd->setLogicalId($to);
        $roseeCmd->save();
    }
}

function rosee_remove() {
    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}
?>
