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
$('#bt_selectTempCmd').on('click', function () {
	jeedom.cmd.getSelectModal({
		cmd: {
			type: 'info',
			subType: 'numeric'
		}
	}, function (result) {
		$('.eqLogicAttr[data-l2key=temperature]').atCaret('insert', result.human);
	});
});
$('#bt_resetSearch').off('click').on('click', function () {
	$('#in_searchEqlogic').val('')
	$('#in_searchEqlogic').keyup();
})
$('#bt_selectHumiCmd').on('click', function () {
	jeedom.cmd.getSelectModal({
		cmd: {
			type: 'info',
			subType: 'numeric'
		}
	}, function (result) {
		$('.eqLogicAttr[data-l2key=humidite]').atCaret('insert', result.human);
	});
});

$('#bt_selectPresCmd').on('click', function () {
	jeedom.cmd.getSelectModal({
		cmd: {
			type: 'info',
			subType: 'numeric'
		}
	}, function (result) {
		$('.eqLogicAttr[data-l2key=pression]').atCaret('insert', result.human);
	});
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=type_calcul]').on('change', function () {
	if ($(this).value() == 'tendance') {
		$('#img_device').attr("src", 'plugins/rosee/core/config/img/' + $(this).value() + '.png');
	} else {
		$('#img_device').attr("src", 'plugins/rosee/plugin_info/rosee_icon.png');
	}
});

$("#table_cmd").sortable({
	axis: "y",
	cursor: "move",
	items: ".cmd",
	placeholder: "ui-state-highlight",
	tolerance: "intersect",
	forcePlaceholderSize: true
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
	$('#humidite').show();
	$('#pressure').show();
	$('#DPR').show();
	$('#SHA').show();
	switch ($("#type_calcul").val()) {
		case 'rosee':
			$('#SHA').hide(); //
			break;
		case 'humidityabs':
			$('#DPR').hide(); //
			$('#SHA').hide(); //
			break;
		case 'givre':
			$('#DPR').hide(); //
			break;
		case 'tendance':
			$('#temperature').hide();
			$('#temperature_offset').hide();
			$('#humidite').hide(); //
			$('#DPR').hide(); //
			$('#SHA').hide(); //
			break;
	}
});


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
	tr += '<td>';
	tr += '<span class="cmdAttr" data-l1key="id"></span>';
	tr += '</td>';
	tr += '<td>';
	tr += '<div class="row">';
	tr += '<div class="col-sm-3">';
	tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> Icône</a>';
	tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
	tr += '</div>';
	tr += '<div class="col-sm-8">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
	tr += '</div>';
	tr += '</div>';
	tr += '</td>';
	tr += '<td>';
	tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
	tr += '<span class="cmdAttr subType"  subType="' + init(_cmd.subType) + ' " ></span>';
	tr += '</td>';
	tr += '<td>';
	if (init(_cmd.subType) == 'numeric') {
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width : 90px;display : inline-block;"> ';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width : 90px;display : inline-block;"> ';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="{{Unité}}" style="width : 90px; display:inline-block"></td>';
	}
	tr += '</td>';
	tr += '<td>';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
	if (_cmd.subType == "binary") {
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
	}
	tr += '</td>';
	tr += '<td>';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
	tr += '</tr>';
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));

}