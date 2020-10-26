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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class rosee_Template
{
	public static function getTemplate()
	{
		$return = array('info' => array('numeric' => array()));
		$return['info']['numeric']['tendance'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_desktop_width_#' => '40'),
			'test' => array(
				array('operation' => '#value# == 0', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_0.png>'),
				array('operation' => '#value# == 1', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_1.png>'),
				array('operation' => '#value# == 2', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_2.png>'),
				array('operation' => '#value# == 3', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_3.png>'),
				array('operation' => '#value# == 4', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_4.png>'),
				array('operation' => '#value# == 5', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_5.png>')
			)
		);

		$return['info']['numeric']['tendance 80x80'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_desktop_width_#' => '80'),
			'test' => array(
				array('operation' => '#value# == 0', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_0.png>'),
				array('operation' => '#value# == 1', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_1.png>'),
				array('operation' => '#value# == 2', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_2.png>'),
				array('operation' => '#value# == 3', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_3.png>'),
				array('operation' => '#value# == 4', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_4.png>'),
				array('operation' => '#value# == 5', 'state_light' => '<img src=plugins/rosee/core/template/img/tendance_5.png>')
			)
		);
		return $return;
	}
}
