<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>
<?php
$exam_api = Yii::app()->moduleAPI->get('OphCiExamination');
$correspondence_api = Yii::app()->moduleAPI->get('OphCoCorrespondence');
?>
<!-- Show full patient Demographies -->
<div class="oe-patient-popup" id="patient-popup-demographics" style="display:none;">
  <div class="flex-layout flex-top">
    <div class="cols-left">
      <div class="popup-overflow">
        <div class="subtitle">Demographics</div>
        <table class="patient-demographics" style="position: relative; right: 0px;">
          <tbody>
          <tr>
            <td>Born</td>
            <td><b><?php echo ($this->patient->dob) ? $this->patient->NHSDate('dob') : 'Unknown' ?></b> (52y)</td>
          </tr><tr>
            <td>Address</td>
            <td><?php echo $this->patient->getSummaryAddress()?></td>
          </tr><tr>
            <td>Ethnic Group</td>
            <td><?php echo $this->patient->getEthnicGroupString() ?></td>
          </tr><tr>
            <td>Telephone</td>
            <td><?php echo !empty($this->patient->primary_phone) ? $this->patient->primary_phone : 'Unknown'?></td>
          </tr><tr>
            <td>Mobile</td>
            <td>Unknown</td>
          </tr><tr>
            <td>Email</td>
            <td><?php echo !empty($this->patient->primary_phone) ? $this->patient->primary_phone : 'Unknown'?></td>
          </tr><tr>
            <td>Next of kin</td>
            <td>Unknown</td>
          </tr>
          </tbody>
        </table>
      </div><!-- .popup-overflow -->
    </div><!-- .cols-left -->

    <div class="cols-right">

      <div class="popup-overflow">

        <div class="subtitle">&nbsp;</div>

        <table class="patient-demographics" style="position: relative; right: 0px;">
          <tbody>
          <tr>
            <td>General Practitioner</td>
            <td><?php echo ($this->patient->gp) ? $this->patient->gp->contact->fullName : 'Unknown'; ?></td>
          </tr><tr>
            <td>GP Address</td>
            <td><?php echo ($this->patient->gp && $this->patient->gp->contact->address) ? $this->patient->gp->contact->address->letterLine : 'Unknown'; ?></td>
          </tr><tr>
            <td>GP Telephone</td>
            <td><?php echo ($this->patient->gp && $this->patient->gp->contact->primary_phone) ? $this->patient->gp->contact->primary_phone : 'Unknown'; ?></td>
          </tr><tr>
            <td>Optician</td>
            <td>Mr Pink</td>
          </tr>
          </tbody>
        </table>
      </div><!-- .popup-overflow -->
    </div><!-- .cols-right -->
  </div><!-- flex -->
</div>

<!-- Patient Quickloog popup. Show Risks, Medical Data, Management Summary and Problem and Plans -->
<div class="oe-patient-popup" id="patient-summary-quicklook" style="display:none;">
    <div class="situational-awareness flex-layout flex-left flex-top">

        <div class="group" style="display: <?= $exam_api->getLetterVisualAcuityRight($patient) ? 'block' : 'none' ?>">
            <?php
            $lDate =  $exam_api->getLetterVisualAcuityDate($patient, 'left');
            $rDate =  $exam_api->getLetterVisualAcuityDate($patient, 'right');
            if($lDate == $rDate){?>
            <span class="data">R <?php echo $exam_api->getLetterVisualAcuityRight($patient)?></span>
            <span class="data"><?php echo $exam_api->getLetterVAMethodName($patient, 'right')?></span>
            <span class="data">L <?php echo $exam_api->getLetterVisualAcuityLeft($patient)?></span>
            <span class="data"><?php echo $exam_api->getLetterVAMethodName($patient, 'left')?></span>
            <span class="oe-date" style="text-align: left;"><?php echo Helper::convertDate2NHS($rDate);?></span>
            <?php } else {?>
            <span class="data">R <?php echo $exam_api->getLetterVisualAcuityRight($patient)?></span>
            <span class="oe-date"><?php echo Helper::convertDate2NHS($rDate);?></span>
            <span class="data">L <?php echo $exam_api->getLetterVisualAcuityLeft($patient)?></span>
            <span class="oe-date" style="text-align: left"><?php echo Helper::convertDate2NHS($lDate);?></span>
            <?php } ?>
        </div>
        <div class="group" style="display: <?= $exam_api->getLetterVisualAcuityRight($patient) ? 'none' : 'block' ?>">
                <span class="data-value not-available">Not Available</span>
        </div>

        <div class="group">
            <?php
                if($correspondence_api->getLastRefraction($patient, 'left') != null){?>
            <span class="data">R <?php echo $correspondence_api->getLastRefraction($patient, 'right')?></span>
            <span class="data">L <?php echo $correspondence_api->getLastRefraction($patient, 'left')?></span>
            <span class="oe-date" style="text-align: left"><?php echo  Helper::convertDate2NHS($correspondence_api->getLastRefractionDate($patient))?></span>
            <?php } else { ?>
                    <span class="data-value not-available">Not Available</span>
            <?php }?>
        </div>

        <div class="group">
            <span class="data">CVI Status:  <?php echo explode("(",$this->cviStatus)[0]; ?></span>
            <span class="oe-date"><?php echo Helper::convertDate2NHS($this->patient->getOphInfo()->cvi_status_date );?></span>
        </div>
    </div>
    <div class="flex-layout flex-top">
      <!-- oe-popup-overflow handles scrolling if data overflow height -->
      <div class="oe-popup-overflow quicklook-data-groups">
        <div class="group">
          <div class="label">Eye diagnoses</div>
          <div class="data">
            <table>
              <tbody>
            <?php
             $ophthalmic_diagnoses = $this->patient->getOphthalmicDiagnosesSummary();
             if (count($ophthalmic_diagnoses)===0) { ?>
               <div style="font-style: italic; color: rgba(255,255,255,0.5);">Nil recorded</div>
             <?php }
             foreach ($ophthalmic_diagnoses as $ophthalmic_diagnosis) {
               list($side, $name, $date) = explode("~", $ophthalmic_diagnosis, 3); ?>
                 <tr>
                   <td><?= $name ?></td>
                   <td>
                       <?php $this->widget('EyeLateralityWidget', array('laterality' => $side)) ?>
                   </td>
                   <td><span class="oe-date"><?= Helper::convertDate2HTML($date) ?></span></td>
                 </tr>
            <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <!-- group-->
        <div class="group">
          <div class="label">Systemic Diagnoses</div>
          <div class="data">
            <table>
              <tbody>
              <?php if (sizeof($this->patient->systemicDiagnoses)==0){ ?>
                <div style="font-style: italic; color: rgba(255,255,255,0.5);">Nil recorded</div>
              <?php }
              foreach ($this->patient->systemicDiagnoses as $diagnosis) { ?>
                <tr>
                  <td> <?php echo $diagnosis->disorder->term?></td>
                  <td>
                    <?php $this->widget('EyeLateralityWidget', array('eye' => $diagnosis->eye)) ?>
                  </td>
                  <td><span class="oe-date"><?= Helper::convertDate2HTML($diagnosis->dateText) ?></span></td>
                </tr>
              <?php }?>
              </tbody>
            </table>

          </div><!-- data -->
        </div>
        <!-- group -->
        <div class="group">
          <div class="label">CVI Status</div>
          <div class="data">
              <?php echo $this->cviStatus; ?>
          </div>
        </div>
        <!-- group-->
      </div><!-- popup-overflow -->

      <!-- oe-popup-overflow handles scrolling if data overflow height -->
      <div class="oe-popup-overflow quicklook-data-groups">
        <!-- Data -->
        <div class="group">
          <div class="label">Eye procedures</div>
          <div class="data">
              <?php $this->widget('OEModule\OphCiExamination\widgets\PastSurgery', array(
                  'patient' => $this->patient,
                  'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE
              )); ?>
          </div>
        </div>
        <!-- group-->
          <?php $this->widget('OEModule\OphCiExamination\widgets\HistoryMedications', array(
              'patient' => $this->patient,
              'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE,
          )); ?>

        <!-- group-->

        <!-- group-->

        <div class="group">
          <div class="label">Family</div>
          <div class="data">
              <?php $this->widget('OEModule\OphCiExamination\widgets\FamilyHistory', array(
                  'patient' => $this->patient,
                  'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE
              )); ?>
          </div>
        </div>
        <!-- group-->

        <div class="group">
          <div class="label">Social</div>
          <div class="data">
              <?php $this->widget('OEModule\OphCiExamination\widgets\SocialHistory', array(
                  'patient' => $this->patient,
                  'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE
              )); ?>
          </div>
        </div>
      <!-- group-->
      </div><!-- 	.oe-popup-overflow -->

    </div><!-- .flex-layout -->
  </div>
<!-- .row -->
<!-- .patient-popup-quicklook -->

<div class="oe-patient-popup" id="patient-popup-management" style="display: none;">

  <div class="flex-layout flex-top">

    <div class="cols-left">

      <div class="popup-overflow">

        <div class="subtitle">Management Summaries</div>

        <ul class="management-summaries">

        </ul>

      </div><!-- .popup-overflow -->

    </div><!-- left -->

    <div class="cols-right">

      <div class="popup-overflow">

        <div class="problems-plans">

          <div class="subtitle">Problems &amp; Plans</div>

          <ul class="problems-plans-sortable" id="problems-plans-sortable">

          </ul>
          <div class="create-new-problem-plan">
            <input id="create-problem-plan" type="text" placeholder="Add Problem or Plan">
            <div class="add-problem-plan-btn tiny" id="js-add-pp-btn"><i class="oe-i plus pro-theme"></i></div>
          </div>
            
        </div>
      </div>
      <!-- .oe-popup-overflow -->
    </div>
    <!-- .col-right -->
  </div>
  <!-- .row -->
<!--  Old Code -->
    <!-- Patient icon -->
    <button
        class="hide toggle-patient-summary-popup icon-patient-patient-id_small<?= count($this->warnings) ? '-warning' : ''; ?>">
        Toggle patient summary
    </button>

    <!-- Quicklook icon -->
    <button
        class="toggle-patient-summary-popup icon-alert-quicklook"
        data-hide-icon="icon-alert-cross"
        data-show-icon="icon-alert-quicklook">
        Toggle patient summary
    </button>

    <div class="panel patient-popup" id="patient-summary-popup">
        <!-- Help hint -->
        <span
            class="help-hint"
            data-text='{
                "close": {
                    "full": "Click to close",
                    "short": "Close"
                },
                "lock": {
                    "full": "Click to lock",
                    "short": "Lock"
                }
            }'>
            Click to lock
        </span>

        <div class="zone2">
            <div class="row">
                <div class="large-2 column label">Born</div>
                <div class="large-10 column">
                    <b><?= ($this->patient->dob) ? $this->patient->NHSDate('dob') : 'Unknown' ?></b>
                    <?= $this->patient->dob ? '(' . $this->patient->getAge() . 'y' .
                            ($this->patient->isDeceased() ? ' - Deceased' : '') . ')'
                        : '' ?>
                </div>
            </div>
            <div class="row">
                <div class="large-2 column label">Address</div>
                <div class="large-10 column data"><?= $this->patient->getSummaryAddress(', ') ?></div>
            </div>
        </div>
        
        <!-- Warnings -->
        <?php if ($this->warnings) { ?>
            <div class="alert-box patient with-icon">
                <span>
                    <?php foreach ($this->warnings as $warn) { ?>
                        <strong><?php echo $warn['long_msg']; ?></strong>
                        - <?php echo $warn['details']; ?><br />
                    <?php } ?>
                </span>
            </div>
        <?php } ?>
        <div class="oe-popup-overflow">
            <div class="summary-data">
              <?php if ($this->ophthalmicDiagnoses) { ?>
                <div class="row">
                  <div class="large-2 column label">
                    Ophthalmic Diagnoses
                  </div>
                  <div class="large-10 column data">
                      <?php echo $this->ophthalmicDiagnoses; ?>
                  </div>
                </div>
                <?php } ?>
                <?php if ($this->systemicDiagnoses) { ?>
                  <div class="row">
                    <div class="large-2 column label">
                      Systemic Diagnoses
                    </div>
                    <div class="large-10 column data">
                        <?php echo $this->systemicDiagnoses; ?>
                    </div>
                  </div>
                <?php } ?>
              <div class="row">
                <div class="large-2 column label">
                  CVI Status
                </div>
                <div class="large-10 column data">
                    <?php echo $this->cviStatus; ?>
                </div>
              </div>
                <?php if ($this->operations) { ?>
                  <div class="row surgical-history">
                    <div class="large-2 column label">
                      Surgical History
                    </div>
                    <div class="large-10 column data">
                        <?php echo $this->operations; ?>
                    </div>
                  </div>
                <?php } ?>

    </div><!-- .cols-right -->
  </div><!-- flex -->

</div>

<div class="oe-patient-popup" id="patient-popup-allergies-risks" style="display: none;">
  <div class="flex-layout flex-top">
    <div class="cols-left">

      <!-- Warnings: Allergies -->
      <div class="popup-overflow">
            <?php $this->widget('OEModule\OphCiExamination\widgets\Allergies', array(
                'patient' => $this->patient,
                'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE
            )); ?>
      </div><!-- .popup-overflow -->

    </div><!-- cols-left -->
    <div class="cols-right">
      <!-- Warnings: Risks -->
      <div class="popup-overflow">
          <?php $this->widget('OEModule\OphCiExamination\widgets\HistoryRisks', array(
              'patient' => $this->patient,
              'mode' => BaseEventElementWidget::$PATIENT_SUMMARY_MODE
          )); ?>
        <table class="risks alert-box patient">
          <tbody>
          <?php $diabetes_disorders = $this->patient->getDisordersOfType(Disorder::$SNOMED_DIABETES_SET);
          foreach ($diabetes_disorders as $diabete) { ?>
                <tr>
                  <td><?= $diabete->term ?></td>
                  <td></td>
                </tr>
          <?php } ?>
          </tbody>
        </table>
      </div><!-- .popup-overflow -->
    </div><!-- .col-right -->
  </div><!-- .flex -->
</div>