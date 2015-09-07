<?php
namespace app\models;

use yii\base\Model;

/**
* Class UploadForm
* @property string $filename source filename from client
*/
class UploadForm extends Model
{
    /**
    * @var mixed image the attribute for rendering the file input
    * widget for upload on the form
    */
    //public $image;
    public $filename;
    public $savedTo;

    public $images;

    public function rules()
    {
        return [
            [['filename'], 'safe'],
            //[['filename'], 'file', 'extensions'=>'jpg, gif, png'],
            [['filename'], 'file', 'extensions'=>'jpg'],
        ];
    }
}