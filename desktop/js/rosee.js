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
                                    if (data.state != 'ok') {
                                        $('#div_alert').showAlert({
                                            message: data.result,
                                            level: 'danger'
                                        });
                                        return;
                                    }
                                    $('#div_alert').showAlert({
                                        message: '{{Opération réalisée avec succès}}',
                                        level: 'success'
                                    });
                                    $('.eqLogicDisplayCard[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
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
    var text = $("#type_calcul").val();
    if ((text == '')) {
        $('#temperature').hide();
        $('#temperature_offset').hide();
        $('#humidite').hide();
        $('#pressure').hide();
        $('#DPR').hide();
        $('#SHA').hide();
    }
    if ((text == 'rosee_givre')) {
        $('#temperature').show();
        $('#temperature_offset').show();
        $('#humidite').show();
        $('#pressure').show();
        $('#DPR').show();
        $('#SHA').show();
    }
    if ((text == 'rosee')) {
        $('#temperature').show();
        $('#temperature_offset').show();
        $('#humidite').show();
        $('#pressure').show();
        $('#DPR').show();
        $('#SHA').hide();
    }
    if ((text == 'humidityabs')) {
        $('#temperature').show();
        $('#temperature_offset').show();
        $('#humidite').show();
        $('#pressure').show();
        $('#DPR').hide();
        $('#SHA').hide();
    }
    if ((text == 'givre')) {
        $('#temperature').show();
        $('#temperature_offset').show();
        $('#humidite').show();
        $('#pressure').show();
        $('#DPR').hide();
        $('#SHA').show();
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

    if (init(_cmd.type) == 'info') {
        var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'readonly' : '';
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<div class="row">';
        tr += '<div class="col-sm-4">';
        if (_cmd.subType == "numeric" || _cmd.subType == "binary") {
            tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> Icône</a>';
            tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
        }
        tr += '</div>';
        tr += '<div class="col-sm-8">';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
        tr += '</div>';
        tr += '</div>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="value" ' + disabled + ' readonly=true>';
        tr += '</td>';
        if (_cmd.subType == "numeric") {
            tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="unite" style="width : 90px;" placeholder="{{Unité}}"></td>';
        } else {
            tr += '<td></td>';
        }
        tr += '<td>';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
        if (_cmd.subType == "numeric") {
            tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        }
        if (_cmd.subType == "binary") {
            tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
        }
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
        }
        tr += '</td>';
        tr += '<td>';
        tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
}
