<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

$this->title = 'Uploaded';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('imageUploaded')): ?>

    <div class="alert alert-success">
        File Uploaded
    </div>



    <?php elseif (Yii::$app->session->hasFlash('imageNotUploaded')): ?>

    <div class="alert alert-danger">
        File NOT Uploaded
    </div>


    <?php endif; ?>

    <pre>
        <?php  //var_dump($model) ?>
    </pre>
</div>
