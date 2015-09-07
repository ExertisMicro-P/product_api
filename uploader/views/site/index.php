<?php
use yii\helpers\Html;

use kartik\widgets\ActiveForm; // or yii\widgets\ActiveForm
use kartik\widgets\FileInput;

use yii\helpers\Url;


/* @var $this yii\web\View */
$this->title = 'Product API Image uploader';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Product API</h1>

        <p class="lead">Upload Product Image Files.</p>

        <?php
        if (Yii::$app->user->isGuest) {
            echo "<p>Please ".Html::a('Login', Url::toRoute('site/login'), ['class'=>'btn btn-primary'])." to use this uploader</p>";
        }
        ?>
<?php

if (!Yii::$app->user->isGuest) {

$form = ActiveForm::begin([
    'options'=>['enctype'=>'multipart/form-data'] // important
]);
//echo $form->field($model, 'filename');

// your fileinput widget for single file upload
echo $form->field($model, 'filename')->widget(FileInput::classname(), [
    'options'=>['accept'=>'image/*'],
    'pluginOptions'=>[
        'allowedFileExtensions'=>['jpg'/*,'gif','png'*/],
        'mainClass' => 'input-group-lg'
        ]
]);

echo FileInput::widget([
    'model' => $model,
    'attribute' => 'images[]',
    'options' => ['multiple' => true]
]);
/**
 * uncomment for multiple file upload
 * RCH 20141121 - couldn't get this to work, wouldn't populate the $_POST
 *

echo $form->field($model, 'image[]')->widget(FileInput::classname(), [
    'options'=>['accept'=>'image/*', 'multiple'=>true],
    'pluginOptions'=>['allowedFileExtensions'=>['jpg','gif','png']
]);
*
*/
/*echo Html::submitButton('Upload' , [
    'class'=>'btn btn-success']
);
 *
 */
ActiveForm::end();

}
?>
</div>
