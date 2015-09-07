<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use app\models\UploadForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

   /* public function actionIndex()
    {
        return $this->render('index');
    }
    *
    */


    /**
     *  See uploader\models\User.php for list of users/user credentials
     * @return type
     */
    public function actionLogin()
    {
        // 'cos I can't install mcrypt on ma-webproxy-04 right now
        //Yii::$app->request->enableCsrfValidation = false;


        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionUploaded($model)
    {

    }


    public function actionIndex()
    {
        // 'cos I can't install mcrypt on ma-webproxy-04 right now
        //Yii::$app->request->enableCsrfValidation = false;

        $model = new UploadForm;
        if ($model->load(Yii::$app->request->post())) {
            // get the uploaded file instance. for multiple file uploads
            // the following data will return an array
            $image = UploadedFile::getInstance($model, 'filename');
            $images = UploadedFile::getInstance($model, 'images');

            // store the source file name
            $model->filename = $image->name;
            $ext = end((explode(".", $image->name)));

            // generate a unique file name
            //$model->avatar = Yii::$app->security->generateRandomString().".{$ext}";

            // the path to save file, you can set an uploadPath
            // in Yii::$app->params (as used in example below)
            // remove anything after the @
            $path_parts = pathinfo($model->filename);
            $splitforrawcode = explode('@', $path_parts['filename']);
            $partcode = $splitforrawcode[0];

            $savefilename = $partcode.'.'.$path_parts['extension'];
            $savefilename = str_replace('+', ' ', $savefilename);

            $path = Yii::$app->params['uploadPath'] . $savefilename;

            if ($image->saveAs($path)) {
                $model->savedTo = $path;
                Yii::$app->session->setFlash('imageUploaded');
                return $this->render('uploaded', ['model'=>$model]);
            } else {
                 Yii::$app->session->setFlash('imageNotUploaded');
                 return $this->render('uploaded', ['model'=>$model]);
            }

            return $this->redirect(['uploaded', $model]);

        }
        return $this->render('index', [
            'model'=>$model,
        ]);
    } // actionIndex
}
