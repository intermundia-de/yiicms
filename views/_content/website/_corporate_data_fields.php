<?php

use unclead\multipleinput\MultipleInput; ?>


<div id="corporateDataPanel" class="panel-group" role="tablist">
  <div class="panel panel-default">
    <div id="corporateDataHeading" class="panel-heading collapsed" role="tab" data-toggle="collapse"
         data-parent="#corporateDataPanel"
         data-target="#corporateDataCollapse"
         aria-controls="corporateDataCollapse">
      <h3 class="panel-title"><?php echo Yii::t('intermundiacms', 'Corporate Data') ?>
        <i class="fa fa-plus-square icon-collapsed pull-right"></i>
        <i class="fa fa-minus-square icon-expanded pull-right"></i>
      </h3>
    </div>
    <div id="corporateDataCollapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="#corporateDataHeading">
      <div class="panel-body">
          <?php echo $form->field($model, 'company_country')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_city')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_street_address')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'company_postal_code')->textInput(['maxlength' => true]) ?>
          <?php echo $form->field($model, 'location_latitude')->textInput() ?>
          <?php echo $form->field($model, 'location_longitude')->textInput() ?>

        <table id="workingHours" class="table table-bordered">
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
          <?php foreach ($model->getWeekDays() as $day): ?>
            <tr>
              <td>
                <p><?php echo $day ?></p>
              </td>
              <td>
                  <?php echo $form->field($model, "businessHoursShedule[{$day}][startTime]")->widget(\kartik\time\TimePicker::class, [
                      'pluginOptions' => [
                          'showMeridian' => false,
                          'defaultTime' => false
                      ]])->label(false) ?>
              </td>
              <td>
                  <?php echo $form->field($model, "businessHoursShedule[{$day}][endTime]")->widget(\kartik\time\TimePicker::class, [
                      'pluginOptions' => [
                          'showMeridian' => false,
                          'defaultTime' => false
                      ]])->label(false) ?>
              </td>
            </tr>
          <?php endforeach; ?>
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