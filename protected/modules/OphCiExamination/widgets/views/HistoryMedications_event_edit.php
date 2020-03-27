<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2019
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2019, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>

<?php
$model_name = CHtml::modelName($element);
$route_options = CHtml::listData($element->getRouteOptions(), 'id', 'term');
$frequency_options = array();
foreach ($element->getFrequencyOptions() as $k => $v) {
    $frequency_options[$v->id] = $v->term." (".$v->code.")";
}
$stop_reason_options = CHtml::listData($element->getStopReasonOptions(), 'id', 'name');
$element_errors = $element->getErrors();
$laterality_options = Chtml::listData($element->getLateralityOptions(), 'id', 'name');
$unit_options = CHtml::listData(MedicationAttribute::model()->find("name='UNIT_OF_MEASURE'")->medicationAttributeOptions, 'description', 'description');

$current_entries = [];
$stopped_entries = [];
foreach ($element->entries as $entry) {
    if ($entry->originallyStopped) {
        $stopped_entries[] = $entry;
    } else {
        $current_entries[] = $entry;
    }
}
?>

<script type="text/javascript" src="<?= $this->getJsPublishedPath('HistoryRisks.js') ?>"></script>
<script type="text/javascript" src="<?= $this->getJsPublishedPath('HistoryMedications.js') ?>"></script>
<div class="element-fields full-width" id="<?= $model_name ?>_element">
  <div class="data-group full">
    <input type="hidden" name="<?= $model_name ?>[present]" value="1" />
      <input type="hidden" name="<?= $model_name ?>[present]" value="1"/>
      <input type="hidden" name="<?= $model_name ?>[do_not_save_entries]" class="js-do-not-save-entries" value="<?php echo (int)$element->do_not_save_entries; ?>"/>
      <table id="<?= $model_name ?>_entry_table" class="js-entry-table medications js-current-medications">
          <colgroup>
              <col class="cols-2">
              <col class="cols-6">
              <col class="cols-3">
              <col class="cols-icon" span="2">
              <!-- actions auto-->
          </colgroup>
        <thead style= <?php echo !sizeof($current_entries)?  'display:none': ''; ?> >
          <tr>
              <th>Drug</th>
              <th>Dose/frequency/route/start/stop</th>
              <th>Comments</th>
              <th></th>
              <th></th>
          </tr>
          </thead>
        <tbody>
        <?php
                $row_count = 0;
        $total_count = count($current_entries);
        foreach ($current_entries as $row_count => $entry) {
            if ($entry->prescription_item_id) {
                $this->render(
                    'HistoryMedicationsEntry_prescription_event_edit',
                    array(
                        'entry' => $entry,
                        'form' => $form,
                        'model_name' => $model_name,
                        'field_prefix' => $model_name . '[entries][' . $row_count . ']',
                        'row_count' => $row_count,
                        'stop_reason_options' => $stop_reason_options,
                        'usage_type' => 'OphCiExamination',
                        'patient' => $this->patient,
                                                'stopped' => false,
                    )
                );
            } else {
                $this->render(
                    'HistoryMedicationsEntry_event_edit',
                    array(
                        'entry' => $entry,
                        'form' => $form,
                        'allergy_ids' => '',
                        'model_name' => $model_name,
                        'field_prefix' => $model_name . '[entries][' . $row_count . ']',
                        'row_count' => $row_count,
                        'stop_reason_options' => $stop_reason_options,
                        'laterality_options' => $laterality_options,
                        'route_options' => $route_options,
                        'frequency_options' => $frequency_options,
                        'removable' => true,
                        'direct_edit' => false,
                        'usage_type' => 'OphCiExamination',
                        'row_type' => '',
                        'is_last' => ($row_count == $total_count - 1),
                        'is_new' => $entry->getIsNewRecord(),
                        'patient' => $this->patient,
                        'unit_options' => $unit_options,
                                                'stopped' => false,
                    )
                );
            }
                    $row_count++;
        }
        ?>
        </tbody>
    </table>
        <div class="collapse-data" style="<?php echo !sizeof($stopped_entries)?  'display:none': ''; ?>">
            <div class="collapse-data-header-icon expand">
                Stopped Medications <small class="js-stopped-medications-count">(<?=count($stopped_entries);?>)</small>
            </div>
            <div class="collapse-data-content" style="display: none;">

                <table id="<?= $model_name ?>_stopped_entry_table" class="medications js-entry-table js-stopped-medications">
                    <colgroup>
                        <col class="cols-2">
                        <col class="cols-6">
                        <col class="cols-3">
                        <col class="cols-icon" span="2">
                    </colgroup>
                    <thead>
                        <tr style='display:none'>
                            <th>Drug</th>
                            <th>Dose/frequency/route/start/stop</th>
                            <th>Comments</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($stopped_entries as $entry) {
                        if ($entry->prescription_item_id) {
                            $this->render(
                                'HistoryMedicationsEntry_prescription_event_edit',
                                array(
                                    'entry' => $entry,
                                    'form' => $form,
                                    'model_name' => $model_name,
                                    'field_prefix' => $model_name . '[entries][' . $row_count . ']',
                                    'row_count' => $row_count,
                                    'stop_reason_options' => $stop_reason_options,
                                    'usage_type' => 'OphCiExamination',
                                    'patient' => $this->patient,
                                    'stopped' => true,
                                )
                            );
                        } else {
                            $this->render(
                                'HistoryMedicationsEntry_event_edit',
                                array(
                                    'entry' => $entry,
                                    'form' => $form,
                                    'allergy_ids' => '',
                                    'model_name' => $model_name,
                                    'field_prefix' => $model_name . '[entries][' . $row_count . ']',
                                    'row_count' => $row_count,
                                    'stop_reason_options' => $stop_reason_options,
                                    'laterality_options' => $laterality_options,
                                    'route_options' => $route_options,
                                    'frequency_options' => $frequency_options,
                                    'removable' => true,
                                    'direct_edit' => false,
                                    'usage_type' => 'OphCiExamination',
                                    'row_type' => '',
                                    'is_last' => ($row_count == $total_count - 1),
                                    'is_new' => $entry->getIsNewRecord(),
                                    'patient' => $this->patient,
                                    'unit_options' => $unit_options,
                                    'stopped' => true,
                                )
                            );
                        }
                        $row_count++;
                    }
                    ?>
                    </tbody>
                </table>

            </div>
        </div>
  </div>
  <div class="flex-layout flex-right">
    <div class="add-data-actions flex-item-bottom" id="medication-history-popup">
      <button class="button hint green js-add-select-search" id="add-medication-btn" type="button">
        <i class="oe-i plus pro-theme"></i>
      </button>
    </div>
  </div>
    <script type="text/template" class="entry-template hidden" id="<?= CHtml::modelName($element).'_entry_template' ?>">
        <?php
        $empty_entry = new EventMedicationUse();
        $this->render(
            'HistoryMedicationsEntry_event_edit',
            array(
                'entry' => $empty_entry,
                'form' => $form,
                'allergy_ids' => '{{allergy_ids}}',
                'model_name' => $model_name,
                'field_prefix' => $model_name . '[entries][{{row_count}}]',
                'row_count' => '{{row_count}}',
                'removable' => true,
                'direct_edit' => true,
                'route_options' => $route_options,
                'frequency_options' => $frequency_options,
                'stop_reason_options' => $stop_reason_options,
                'laterality_options' => $laterality_options,
                'usage_type' => 'OphCiExamination',
                'row_type' => 'new',
                'is_last' => false,
                'is_new' => true,
                'patient' => $this->patient,
                                'unit_options' => $unit_options,
                                'is_template' => true,
                            'stopped' => false,
            )
        );
        ?>
    </script>
</div>
<script type="text/javascript">
    $('#<?= $model_name ?>_element').closest('section').on('element_removed', function() {
        delete window.HMController;
    });
    var medicationsController;
    $(document).ready(function () {
        medicationsController = new OpenEyes.OphCiExamination.HistoryMedicationsController({
            element: $('#<?=$model_name?>_element'),
            patientAllergies: <?= CJSON::encode($this->patient->getAllergiesId()) ?>,
            allAllergies: <?= CJSON::encode(CHtml::listData(\OEModule\OphCiExamination\models\OphCiExaminationAllergy::model()->findAll(), 'id', 'name')) ?>,
            onInit: function (controller) {
                registerElementController(controller, "HMController", "MMController");
                /* Don't add automatically
                if (typeof controller.MMController === "undefined" && $("#OEModule_OphCiExamination_models_MedicationManagement_element").length === 0)  {
                    var sidebar = $('aside.episodes-and-events').data('patient-sidebar');
                    sidebar.addElementByTypeClass('OEModule_OphCiExamination_models_MedicationManagement');
                }
                */
            },
            onAddedEntry: function ($row, controller) {
                if (typeof controller.MMController !== "undefined") {
                    var data = $row.data("medication");
                    if(!$row.hasClass("new")){
                        data.locked = 1;
                    }
                    if(data.will_copy) {
                        new_rows = controller.MMController.addEntry([data], false);
                        // This callback is called once per Medication added
                        // to HM therefore the returned new_row length must be 1
                        if (new_rows && new_rows.length === 1) {
                            $new_row = $(new_rows[0]);
                            controller.disableRemoveButton($new_row);
                            controller.bindEntries($row, $new_row);
                        }
                    }
                }
            }
        });

        $(document).on("click", ".alt-display-trigger", function (e) {
            e.preventDefault();
            $(e.target).prev(".alternative-display").find(".textual-display").trigger("click");
        });

        $('#<?= $model_name ?>_element').closest('section').on('element_removed', function () {
            if (typeof window.MMController !== "undefined") {
                window.MMController.$table.find('tr').each(function () {
                                        window.MMController.enableManualRowDeletion($(this));
                    if (typeof $(this).data('bound_entry') !== 'undefined') {
                        $(this).removeData('bound_entry');
                    }
                });
            }
        });

        <?php

        $common_systemic = Medication::model()->listCommonSystemicMedications(true);
        foreach ($common_systemic as &$medication) {
            $medication['prepended_markup'] = $this->widget('MedicationInfoBox', array('medication_id' => $medication['id']), true);
        }

        $firm_id = $this->getApp()->session->get('selected_firm_id');
        $site_id = $this->getApp()->session->get('selected_site_id');
        if ($firm_id) {
            /** @var Firm $firm */
            $firm = $firm_id ? Firm::model()->findByPk($firm_id) : null;
            $subspecialty_id = $firm->getSubspecialtyID();
            $common_ophthalmic = Medication::model()->listBySubspecialtyWithCommonMedications($subspecialty_id, true, $site_id);
            foreach ($common_ophthalmic as &$medication) {
                $medication['prepended_markup'] = $this->widget('MedicationInfoBox', array('medication_id' => $medication['id']), true);
            }
        } else {
            $common_ophthalmic = array();
        }

        ?>
        new OpenEyes.UI.AdderDialog({
            openButton: $('#add-medication-btn'),
            itemSets: [
                new OpenEyes.UI.AdderDialog.ItemSet(<?= CJSON::encode(
                    $common_systemic) ?>, {'multiSelect': true, header: "Common Systemic"})
                ,
                new OpenEyes.UI.AdderDialog.ItemSet(<?= CJSON::encode(
                    $common_ophthalmic) ?>, {'multiSelect': true, header: "Common Ophthalmic"})
            ],
            onReturn: function (adderDialog, selectedItems) {
                medicationsController.addEntry(selectedItems, true);
                return true;
            },
            searchOptions: {
                searchSource: medicationsController.options.searchSource,
            },
            enableCustomSearchEntries: true,
            searchAsTypedItemProperties: {id: "<?php echo EventMedicationUse::USER_MEDICATION_ID ?>"},
            booleanSearchFilterEnabled: true,
            booleanSearchFilterLabel: 'Include branded',
            booleanSearchFilterURLparam: 'include_branded'
        });

        let elementHasRisks = <?= $element->hasRisks() ? 1 : 0 ?>;
        if (elementHasRisks && !$('.' + OE_MODEL_PREFIX + 'HistoryRisks').length) {
                $('#episodes-and-events').data('patient-sidebar').addElementByTypeClass(OE_MODEL_PREFIX + 'HistoryRisks', undefined);
        }
    });
</script>
