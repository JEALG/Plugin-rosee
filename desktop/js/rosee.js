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

/*
* Permet la réorganisation des commandes dans l'équipement
*/
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

/*
* Fonction Spécifique Plugin
*/
$('#div_pageContainer').off('click','.listCmdActionOther').on('click','.listCmdActionOther', function () {
	var el = $(this);
	jeedom.cmd.getSelectModal({cmd: {type: 'info',subType : 'numeric'}}, function (result) {
	  el.closest('.input-group').find('input').value(result.human);
	});
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=type_calcul]').on('change', function () {
	if ($(this).value() == 'tendance' || $(this).value() == 'temperature') {
		$('#img_device').attr("src", 'plugins/rosee/core/config/img/' + $(this).value() + '.png');
	} else {
		$('#img_device').attr("src", 'plugins/rosee/plugin_info/rosee_icon.png');
	}
});

$('#bt_autoDEL_eq').on('click', function () {
	var dialog_title = '{{Recréer les commandes}}';
	var dialog_message = '<form class="form-horizontal onsubmit="return false;">';
	dialog_title = '{{Recréer les commandes}}';
	dialog_message += '<label class="lbl lbl-warning" for="name">{{Attention, cela va supprimer les commandes existantes.}}</label> ';
	dialog_message += '</form>';
	bootbox.dialog({
		title: dialog_title,
		message: dialog_message,
		buttons: {
			"{{Annuler}}": {
				className: "btn-danger",
				callback: function () {}
			},
			success: {
				label: "{{Démarrer}}",
				className: "btn-success",
				callback: function () {
					bootbox.confirm('{{Etes-vous sûr de vouloir récréer toutes les commandes ? Cela va supprimer les commandes existantes}}', function (result) {
						if (result) {
							$.ajax({
								type: "POST",
								url: "plugins/rosee/core/ajax/rosee.ajax.php",
								data: {
									action: "autoDEL_eq",
									id: $('.eqLogicAttr[data-l1key=id]').value(),
								},
								dataType: 'json',
								error: function (request, status, error) {
									handleAjaxError(request, status, error);
								},
								success: function (data) {
									/*if (data.state != 'ok') {
									    return;
									} */
									$('.eqLogicDisplayCard[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
									$('#div_alert').showAlert({
										message: '{{Opération réalisée avec succès}}',
										level: 'success'
									});
								}
							});
						}
					});
				}
			},
		}
	});
});

$('#type_calcul').change(function () {
	$('#temperature').show();
	$('#temperature_offset').show();
	$('#humidite').hide();
	$('#DPR').hide();
	$('#SHA').hide();
	$('#step1').hide();
	$('#step2').hide();
	$('#wind').hide();
	switch ($("#type_calcul").val()) {
		case 'rosee':
			$('#humidite').show();
			$('#DPR').show();
			$('#pressure').show();
			break;
		case 'humidityabs':
			$('#humidite').show();
			$('#pressure').show();
			break;
		case 'givre':
			$('#humidite').show();
			$('#SHA').show();
			$('#pressure').show();
			break;
		case 'rosee_givre':
			$('#humidite').show();
			$('#DPR').show();
			$('#SHA').show();
			$('#pressure').show();
			break;
		case 'temperature':
			$('#humidite').show();
			$('#pressure').hide();
			$('#wind').show();
			$('#step1').show();
			$('#step2').show();
				break;
		case 'tendance':
			$('#temperature').hide();
			$('#temperature_offset').hide();
			$('#pressure').show();
			break;
	}
});

/*
* Fonction permettant l'affichage des commandes dans l'équipement
*/
function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = {
			configuration: {}
		};
	}
	if (!isset(_cmd.configuration)) {
		_cmd.configuration = {};
	}
	if (init(_cmd.logicalId) == 'refresh') {
		return;
	}
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td style="min-width:50px;width:70px;">';
	tr += '<span class="cmdAttr" data-l1key="id" ></span>';
	tr += '</td>';
	tr += '<td style="min-width:750px;width:850px;">';
	tr += '<div class="row">';
	tr += '<div class="col-xs-9">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}">';
	tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" disabled style="display : none;margin-top : 5px;" title="{{Commande information liée}}">';
	tr += '<option value="">{{Aucune}}</option>';
	tr += '</select>';
	tr += '</div>';
	tr += '<div class="col-xs-3">';
	tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon" title="Changer l\'icône"><i class="fa fa-flag"></i> {{Icône}}</a>';
	tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
	tr += '</div>';
	tr += '</div>';
	tr += '</td>';
	tr += '<td>';
	tr += '<span disabled class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span disabled class="subType" subType="' + init(_cmd.subType) + '"></span>';
	tr += '</td>';
	tr += '<td style="min-width:140px;width:140px;">';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
	tr += '</td>';
	tr += '<td style="min-width:200px;">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min.}}" title="{{Min.}}" style="width:30%;display:inline-block;"/> ';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max.}}" title="{{Max.}}" style="width:30%;display:inline-block;"/> ';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="{{Unité}}" style="width:30%;display:inline-block;"/>';
	tr += '</td>';
	tr += '<td>';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure" title="Configuration avancée"><i class="fas fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test" title="Tester la commande"><i class="fas fa-rss"></i> {{Tester}}</a>';
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="Supprimer la commande"></i></td>';
	tr += '</tr>';
   	$('#table_cmd tbody').append(tr);
   	var tr = $('#table_cmd tbody tr').last();
   	jeedom.eqLogic.builSelectCmd({
     	id:  $('.eqLogicAttr[data-l1key=id]').value(),
     	filter: {type: 'info'},
     	error: function (error) {
       		$('#div_alert').showAlert({message: error.message, level: 'danger'});
     	},
     	success: function (result) {
       	tr.find('.cmdAttr[data-l1key=value]').append(result);
       	tr.setValues(_cmd, '.cmdAttr');
       	jeedom.cmd.changeType(tr, init(_cmd.subType));
     	}
   });
}