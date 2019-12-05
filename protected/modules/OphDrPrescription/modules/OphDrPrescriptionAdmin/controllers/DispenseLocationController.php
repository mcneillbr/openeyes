<?php

/**
 * OpenEyes.
 *
 * (C) OpenEyes Foundation, 2019
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2019, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
class DispenseLocationController extends BaseAdminController
{
    public $group = 'Prescription';

    public function actionIndex()
    {
        $dispense_locations_model = OphDrPrescription_DispenseLocation::model();
        $path = Yii::getPathOfAlias('application.widgets.js');
        $assetManager = Yii::app()->getAssetManager();
        $assetManager->registerScriptFile('//js/oeadmin/OpenEyes.admin.js');
        $assetManager->registerScriptFile('//js/oeadmin/list.js');
        $generic_admin = $assetManager->publish($path . '/GenericAdmin.js');
        Yii::app()->getClientScript()->registerScriptFile($generic_admin);

        $criteria = new \CDbCriteria();
        $criteria->order = 'display_order';
        $dispense_locations = $dispense_locations_model->findAll($criteria);

        $this->render(
            '/admin/dispense_location/index',
            [
                'dispense_locations' => $dispense_locations
            ]
        );
    }

    public function actionEdit($id)
    {
        if (!$model = OphDrPrescription_DispenseLocation::model()->findByPk($id)) {
            $this->redirect(['/OphDrPrescription/admin/DispenseLocation/index']);
        }

        $errors = [];

        $this->saveDispenseLocation($model, $errors, 'Edit');
    }

    public function actionCreate()
    {
        $model = new OphDrPrescription_DispenseLocation();
        $errors = [];
        $this->saveDispenseLocation($model, $errors, 'Create');
    }

    private function saveDispenseLocation($model, $errors, $form_type)
    {
        if (Yii::app()->request->isPostRequest) {
            $model->attributes = $_POST['OphDrPrescription_DispenseLocation'];
            $model->display_order =  isset($model->id) ? $model->display_order : $model->getNextHighestDisplayOrder(1);

            if ($model->save()) {
                $this->redirect(['/OphDrPrescription/admin/DispenseLocation/index']);
            } else {
                $errors = $model->errors;
            }
        }

        $this->render('/admin/edit', [
            'model' => $model,
            'errors' => $errors,
            'title' => $form_type. ' dispense location'
        ]);
    }

    public function actions() {
        return [
            'sortLocations' => [
                'class' => 'SaveDisplayOrderAction',
                'model' => OphDrPrescription_DispenseLocation::model(),
                'modelName' => 'OphDrPrescription_DispenseLocation',
            ],
        ];
    }

}
