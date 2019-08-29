<?php

use unclead\multipleinput\MultipleInput; ?>

<div id="corporateDataPanel" class="panel-group" role="tablist">
  <div class="panel panel-default lobipanel" data-sortable="true">
    <div id="corporateDataHeading" class="panel-heading collapsed" role="tab" data-toggle="collapse"
         data-parent="#corporateDataPanel"
         data-target="#corporateDataCollapse"
         aria-controls="corporateDataCollapse">
      <h3 class="panel-title"><?php echo Yii::t('intermundiacms', 'Corporate Data') ?>
      </h3>
      <div class="panel-controls">
        <i class="fa fa-plus-square icon-collapsed pull-right"></i>
        <i class="fa fa-minus-square icon-expanded pull-right"></i>
      </div>
    </div>
    <div id="corporateDataCollapse" class="panel-collapse collapse" role="tabpanel"
         aria-labelledby="#corporateDataHeading">
      <div class="panel-body">
          <?php echo $form->field($model, 'company_country')->dropDownList(
              array_values(\intermundia\yiicms\models\Country::getDropdownListItems()), [
                  'prompt' => Yii::t('intermundiacms', 'Select country')
          ]) ?>
          <?php echo $form->field($model, 'company_city')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_street_address')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_postal_code')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'location_latitude')->textInput() ?>
          <?php echo $form->field($model, 'location_longitude')->textInput() ?>

        <label class="control-label" for="company_business_hours">
            <?php echo $model->getAttributeLabel('company_business_hours') ?>
        </label>
        <table id="company_business_hours" class="table table-bordered">
          <thead>
          <tr>
            <td>
              <h5><?php echo Yii::t('intermundiacms', 'Day') ?></h5>
            </td>
            <td>
              <h5><?php echo Yii::t('intermundiacms', 'Start Time') ?></h5>
            </td>
            <td>
              <h5><?php echo Yii::t('intermundiacms', 'End Time') ?></h5>
            </td>
          </tr>
          </thead>
          <tbody>
          <?php for ($i = 0; $i < 7; $i++): ?>
            <tr>
              <td>
                <p><?php echo Yii::t('intermundiacms', jddayofweek($i, 1)) ?></p>
              </td>
              <td>
                  <?php echo $form->field($model, "businessHoursShedule[{$i}][startTime]")->widget(\kartik\time\TimePicker::class, [
                      'pluginOptions' => [
                          'showMeridian' => false,
                          'defaultTime' => false
                      ]])->label(false) ?>
              </td>
              <td>
                  <?php echo $form->field($model, "businessHoursShedule[{$i}][endTime]")->widget(\kartik\time\TimePicker::class, [
                      'pluginOptions' => [
                          'showMeridian' => false,
                          'defaultTime' => false
                      ]])->label(false) ?>
              </td>
            </tr>
          <?php endfor; ?>
          </tbody>
        </table>
          <?php echo $form->field($model, 'company_contact_type')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_telephone')->textInput(['maxlength' => true]) ?>

          <?php echo $form->field($model, 'company_social_links')->widget(MultipleInput::className(), [
              'addButtonPosition' => MultipleInput::POS_ROW,
          ]); ?>
      </div>
    </div>
  </div>
</div>