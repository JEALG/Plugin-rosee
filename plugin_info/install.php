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

function rosee_install()
{
    jeedom::getApiKey('rosee');

    config::save('functionality::cron5::enable', 0, 'rosee');
    config::save('functionality::cron10::enable', 0, 'rosee');
    config::save('functionality::cron15::enable', 0, 'rosee');
    config::save('functionality::cron30::enable', 1, 'rosee');
    config::save('functionality::cronHourly::enable', 0, 'rosee');

    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }

    //message::add('Plugin Rosée - Givre - Tendance', 'Merci pour l\'installation du plugin.');
}

function rosee_update()
{
    jeedom::getApiKey('rosee');

    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }

    if (config::byKey('functionality::cron5::enable', 'rosee', -1) == -1) {
        config::save('functionality::cron5::enable', 0, 'rosee');
    }

    if (config::byKey('functionality::cron10::enable', 'rosee', -1) == -1) {
        config::save('functionality::cron10::enable', 0, 'rosee');
    }

    if (config::byKey('functionality::cron15::enable', 'rosee', -1) == -1) {
        config::save('functionality::cron15::enable', 0, 'rosee');
    }

    if (config::byKey('functionality::cron30::enable', 'rosee', -1) == -1) {
        config::save('functionality::cron30::enable', 1, 'rosee');
    }

    if (config::byKey('functionality::cronHourly::enable', 'rosee', -1) == -1) {
        config::save('functionality::cronHourly::enable', 0, 'rosee');
    }

    $plugin = plugin::byId('rosee');
    $eqLogics = eqLogic::byType($plugin->getId());
    foreach ($eqLogics as $eqLogic) {
        updateLogicalId($eqLogic, 'humidityabs', null, '2');
        updateLogicalId($eqLogic, 'rosee', null, '2');
        updateLogicalId($eqLogic, 'givrage', null, '2');
        updateLogicalId($eqLogic, 'td', null, null, 'Message'); // Modification du 7/12/2020
        updateLogicalId($eqLogic, 'td_num', null, null, 'Message numérique'); // Modification du 7/12/2020
        updateLogicalId($eqLogic, 'windchill', null, null, 'Température ressentie'); // Modification du 7/12/2020
        updateLogicalId($eqLogic, 'heat_index', 'humidex', 0, 'Indice de Chaleur (Humidex)', 'DELETE'); // Modification du 7/12/2020
    }

    //resave eqLogics for new cmd:
    try {
        $eqs = eqLogic::byType('rosee');
        foreach ($eqs as $eq) {
            $eq->save();
        }
    } catch (Exception $e) {
        $e = print_r($e, 1);
        log::add('rosee', 'error', 'rosee update ERROR : ' . $e);
    }

    //message::add('Plugin Rosée - Givre - Tendance', 'Merci pour la mise à jour de ce plugin, consultez le changelog.');

    foreach (eqLogic::byType('rosee') as $rosee) {
        $rosee->getInformations();
    }
}

function updateLogicalId($eqLogic, $from, $to, $_historizeRound = null, $name = null, $unite = null)
{
    $command = $eqLogic->getCmd(null, $from);
    if (is_object($command)) {
        if ($to != null) {
            $command->setLogicalId($to);
        }
        if ($_historizeRound != null) {
            log::add('rosee', 'debug', 'Correction arrondi pour : ' . $from . 'Par :' . $_historizeRound);
            $command->setConfiguration('historizeRound', $_historizeRound);
        }
        if ($name != null) {
            //$command->setName($name);
        }
        if ($unite != null) {
            if ($unite == 'DELETE') {
                $unite = null;
            }
            $command->setUnite($unite);
        }
        $command->save();
    }
}

function rosee_remove()
{
    $cron = cron::byClassAndFunction('rosee', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}
