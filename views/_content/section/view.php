<?php

/**
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\query\CountryTranslationQuery
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */

?>
<?php echo $this->render('../content-tree/_model_fields_view', [
    'contentTreeModel' => $contentTreeModel,
]); ?>
<?php echo \common\widgets\DetailView::widget([
    'model' => $model->activeTranslation,
    'attributes' => [
        'title',
        [
            'label' => $model->activeTranslation->attributeLabels()['template'],
            'format' => 'html',
            'value' => '<div class="highlight">
            <pre><code class="html">' . $model->activeTranslation->getEncodedTemplate() . '</code>
            </pre>
        </div>'
        ],
        'description:html',
    ],
]);

?>
