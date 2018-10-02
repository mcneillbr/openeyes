<?php
/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>

<?php echo $this->renderPartial('//admin/_form_errors', array('errors' => $errors)) ?>

<?php
$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
    'id' => 'settingsformaaaaa',
    'enableAjaxValidation' => false,
    'layoutColumns' => array(
        'label' => 2,
        'field' => 5,
    ),
)) ?>

<?php if ($metadata->key == 'city_road_satellite_view') { ?>
    <div class="cols-12 column">
        <div class="alert-box with-icon warning">
            Removes the 2 check-boxes from Examination->Clinical Management->Cataract Surgical Management named "At
            City Road" and "At Satellite"
        </div>
    </div>
<?php } ?>

<div class="cols-4">
    <table class="standard" id="to_location_sites_grid">
        <h2>Edit setting</h2>
        <hr class="divider">
        <thead>
        <colgroup>
            <col class="cols-2">
            <col class="cols-1">
        </colgroup>
        </thead>
        <tbody>
        <tr>
            <td><?= $metadata->name ?></td>
            <td>
                <?php $this->renderPartial(
                    '//admin/_admin_setting_' . strtolower(str_replace(' ', '_', $metadata->field_type->name)),
                    array('metadata' => $metadata)
                ) ?>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="2">
                <?=\CHtml::submitButton(
                    'Save',
                    [
                        'class' => 'button large',
                        'id' => 'et_save',
                    ]
                ); ?>
                <?=\CHtml::button(
                    'Cancel',
                    [
                        'data-uri' => $cancel_uri,
                        'class' => 'button large',
                        'type' => 'button',
                        'name' => 'cancel',
                        'id' => 'et_cancel'
                    ]
                ); ?>
            </td>
        </tr>
        </tfoot>
    </table>
</div>

<?php $this->endWidget() ?>