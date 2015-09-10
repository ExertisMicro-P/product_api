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


    //public function actionUploaded($model)
    public function actionUploaded()
    {
        return $this->render('uploaded');
    }


    public function actionIndex()
    {
        // 'cos I can't install mcrypt on ma-webproxy-04 right now
        //Yii::$app->request->enableCsrfValidation = false;

        $model = new UploadForm;

        if (empty(Yii::$app->params['uploadPath'])) {
            Yii::$app->session->setFlash('error', 'Please configure the upload path') ;

        } elseif ($model->load(Yii::$app->request->post())) {
            return $this->loadImages($model);
        }
        return $this->render('index', [
            'model'=>$model,
        ]);
    } // actionIndex

    /**
     * LOAD IMAGES
     * ===========
     * The path to save file should be configured in uploadPath in
     * Yii::$app->params
     *
     * @param $model
     *
     * @return \yii\web\Response
     */
    private function loadImages ($model) {
        $images = UploadedFile::getInstances($model, 'images');

        foreach ($images as $image) {
            $model->filename = $image->name;
            $path_parts = pathinfo($model->filename);
            $ext = $path_parts['extension'] ;

            $splitforrawcode = explode('@', $path_parts['filename']);
            $partcode = $splitforrawcode[0];

            $savefilename = $partcode.'.'.$path_parts['extension'];
            $savefilename = str_replace('+', ' ', $savefilename);

            $path = Yii::$app->params['uploadPath'] . $savefilename;

            if ($image->saveAs($path)) {
                $model->savedTo = $path;

                Yii::$app->session->setFlash('imageUploaded');

            } else {
                Yii::$app->session->setFlash('imageNotUploaded');
            }

            //return $this->redirect(['site/uploaded', 'model'=>$model]);
            return $this->redirect(['site/uploaded']);
        }
    }
}
