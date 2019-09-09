<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 7/27/18
 * Time: 10:48 AM
 */

/** @var $contentTreeItem  \intermundia\yiicms\models\ContentTree */
/** @var $model  \intermundia\yiicms\models\BaseModel */

$checked = $contentTreeItem->getMenuTreeModel();
$menus = \intermundia\yiicms\models\Menu::find()->all();
?>

<div class="content-view">
    <?php echo $this->render('../_content/' . $contentTreeItem->content_type . '/view', [
        'model' => $model,
        'contentTreeModel' => $contentTreeItem
    ]); ?>
</div>


