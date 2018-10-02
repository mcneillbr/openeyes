<?php
/**
 * (C) OpenEyes Foundation, 2018
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2017, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>

<div class="row divider">
    <h2>Edit setting</h2>
</div>
<div class="cols-full">

    <?php
    $form = $this->beginWidget('BaseEventTypeCActiveForm', array(
        'id' => 'settingsform',
        'enableAjaxValidation' => false,
        'focus' => '#username',
        'layoutColumns' => array(
            'label' => 2,
            'field' => 5,
        ),
    )) ?>

    <table class="cols-full last-left standard">
        <colgroup>
            <col class="cols-1">
            <col class="cols-1">
        </colgroup>
        <tbody>
        <tr>

            <td><?= $metadata->name ?></td>
            <?php if ($metadata->key == 'city_road_satellite_view') : ?>
                <td>
                    <div class="alert-box issue">
                        Removes the 2 check-boxes from Examination->Clinical Management->Cataract Surgical
                        Management named "At
                        City Road" and "At Satellite"
                    </div>
                </td>

            <?php else : ?>
                <td>
                    <?php
                    $this->renderPartial(
                        '_admin_setting_' . strtolower(str_replace(' ', '_', $metadata->field_type->name)),
                        ['metadata' => $metadata]
                    );
                    ?>
                </td>
            <?php endif; ?>
        </tr>
        </tbody>
        <tfoot class="pagination-container">
        <tr>
            <td colspan="2">
                <?php if ($metadata->key != 'city_road_satellite_view') : ?>
                    <?= CHtml::submitButton('Save', [
                            'class' => 'button small',
                            'name' => 'save',
                            'id' => 'et_save'
                        ]
                    );
                    ?>

                    <?= CHtml::submitButton('Cancel', [
                            'class' => 'button small',
                            'name' => 'cancel',
                            'id' => 'et_cancel'
                        ]
                    );
                    ?>

                <?php endif; ?>
            </td>
        </tr>
        </tfoot>
    </table>

    <?php $this->endWidget() ?>
</div>
