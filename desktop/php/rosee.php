<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('rosee'); // Obtenir l'identifiant du plugin
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i>
                <br />
                <span>{{Ajouter}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i><br>
                <span>{{Configuration}}</span>
            </div>
        </div>
        <div class="input-group" style="margin:5px;">
            <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
            <div class="input-group-btn">
                <a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i> </a>
            </div>
        </div>
        <legend><i class="fas fa-umbrella"></i> <i class="icon jeedomapp-weather"></i> {{Mes Points de Rosée, de Givre}}</legend>
        <div class="eqLogicThumbnailContainer">
            <?php
            $status_r = 0;
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                if ($eqLogic->getConfiguration('type_calcul') != 'tendance') {
                    $status_r = 1;
                    echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';
                    if ($eqLogic->getConfiguration('type_calcul') == 'tendance') {
                        echo '<img src="' . $eqLogic->getImage() . '"/>';
                    } else {
                        echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                    }
                    echo '<br>';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                    echo '</div>';
                }
            }
            if ($status_r == 1) {
                //echo '</div>';
            } else {
                echo "<br/><br/><br/><center><span style='color:#767676;font-size:1em;font-weight: bold;margin-left: 10px'>{{Aucun équipement de type Points de Rosée ou de Givre a été créé.}}</span></center>";
            }
            ?>
        </div>
        <legend><i class="fas fa-chart-bar"></i> {{Mes Tendances}}</legend>
        <div class="eqLogicThumbnailContainer">
            <?php
            $status = 0;
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                if ($eqLogic->getConfiguration('type_calcul') == 'tendance') {
                    $status = 1;
                    echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';
                    if ($eqLogic->getConfiguration('type_calcul') == 'tendance') {
                        echo '<img src="' . $eqLogic->getImage() . '"/>';
                    } else {
                        echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                    }
                    echo '<br>';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                    echo '</div>';
                }
            }
            if ($status == 1) {
                echo '</div>';
            } else {
                echo "<br/><br/><br/><center><span style='color:#767676;font-size:1em;font-weight: bold;margin-left: 10px'>{{Aucun équipement de type Tendance a été créé.}}</span></center>";
            }
            ?>
        </div>
    </div>

    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex">
            <span class="input-group-btn">
                <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-warning btn-sm" id="bt_autoDEL_eq"><i class="fas fa-search" title="{{Recréer les commandes}}"></i> {{Recréer les commandes}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>

        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br />
                <form class="form-horizontal col-sm-10">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
                            <div class="col-sm-4">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l\'équipement}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Objet parent}}</label>
                            <div class="col-sm-4">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Catégorie}}</label>
                            <div class="col-sm-10">
                                <?php
                                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                    echo '<label class="checkbox-inline">';
                                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                    echo '</label>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <form class="form-horizontal col-sm-2">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-8">
                                <img src="core/img/no_image.gif" data-original=".png" id="img_device" style="width:120px;" />
                            </div>
                        </div>
                    </fieldset>
                </form>
                <br />

                <hr>

                <legend><i class="fas fa-cog"></i> {{Paramètres}}</legend>
                <form class="form-horizontal col-sm-10">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Type de Calcul}}
                                <sup><i class="fas fa-question-circle" title="{{Il est possible de se limiter à un seul calcul, Point de Rosée et Point de Givre fait tous les calculs}}"></i></sup>
                            </label>
                            <div class="col-sm-4">
                                <select id="type_calcul" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="type_calcul">
                                    <option value=''>{{Aucun}}</option>
                                    <option value='humidityabs'>{{Humidité absolue}}</option>
                                    <option value='givre'>{{Point de Givre}}</option>
                                    <option value='rosee'>{{Point de Rosée}}</option>
                                    <option value='rosee_givre'>{{Point de Rosée et Point de Givre}}</option>
                                    <option value='tendance'>{{Tendance Météo}}</option>
                                </select>
                            </div>
                        </div>
                        <div id="temperature" class="form-group" style="display:none">
                            <label class="col-md-2 control-label">{{Température}}
                                <sup><i class="fas fa-question-circle" title="{{(°C) Commande température}}"></i></sup>
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="temperature" placeholder="{{Température °C}}">
                                    <span class="input-group-btn">
                                        <a class="btn btn-default listCmdActionOther roundedRight" id="bt_selectTempCmd"><i class="fas fa-list-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="temperature_offset" class="form-group" style="display:none">
                            <label class="col-sm-2 control-label">{{Offset Température}}
                                <sup><i class="fas fa-question-circle" title="{{A ajuster en fonction des observations locales et de la position de la sonde, 0 par défaut.}}"></i></sup>
                            </label>
                            <div class="col-md-1">
                                <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="OffsetT" placeholder="0">
                            </div>
                        </div>
                        <div id="humidite" class="form-group" style="display:none">
                            <label class="col-sm-2 control-label">{{Humidité Relative}}
                                <sup><i class="fas fa-question-circle" title="{{(%) Commande humidité}}"></i></sup>
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="humidite" placeholder="{{Humidité Relative %}}">
                                    <span class="input-group-btn">
                                        <a class="btn btn-default listCmdActionOther roundedRight" id="bt_selectHumiCmd"><i class="fas fa-list-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="pressure" class="form-group" style="display:none">
                            <label class="col-sm-2 control-label">{{Pression Atmosphérique}}
                                <sup><i class="fas fa-question-circle" title="{{(hPa) Pression atmosphérique réelle sur le site. 1013.25 hPa par défaut si non renseignée.}}"></i></sup>
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="pression" placeholder="1013.25 hPa">
                                    <span class="input-group-btn">
                                        <a class="btn btn-default listCmdActionOther roundedRight" id="bt_selectPresCmd"><i class="fas fa-list-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="DPR" class="form-group" style="display:none">
                            <label class="col-sm-2 control-label">{{Seuil de l'Alerte Rosée}}
                                <sup><i class="fas fa-question-circle" title="{{(°C) Seuil de déclenchement de l'alerte rosée, 2°C par défaut (dépression du point de rosée T°-Tr°) A ajuster en fonction des observations locales.}}"></i></sup>
                            </label>
                            <div class="col-md-1">
                                <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DPR" placeholder="2.0">
                            </div>
                        </div>
                        <div id="SHA" class="form-group" style="display:none">
                            <label class="col-sm-2 control-label">{{Seuil d'Humidité Absolue}}
                                <sup><i class="fas fa-question-circle" title="{{Seuil d'humidité absolue en dessous duquel il est peu probable qu'il givre, 2.8 par défaut.}}"></i></sup>
                            </label>
                            <div class="col-md-1">
                                <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="SHA" placeholder="2.8">
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="commandtab">
                <br />
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th style="width: 50px;"> ID</th>
                            <th style="width: 550px;">{{Nom}}</th>
                            <th style="width: 250px;">{{Sous-Type}}</th>
                            <th style="width: 350px;">{{Min/Max - Unité}}</th>
                            <th>{{Paramètres}}</th>
                            <th style="width: 250px;">{{Options}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php
include_file('desktop', 'rosee', 'js', 'rosee');
include_file('core', 'plugin.template', 'js');
?>