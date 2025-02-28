<?php


namespace BoltSystem\Yii2Logs\log\base\controllers;


use app\models\Log;
use app\models\User;
use BoltSystem\Yii2Logs\log\action\drivers\LogDb;

class BaseController extends \yii\web\Controller
{
    public $layout = '@vendor/BoltSystem/Yii2Logs/log/base/views/layouts';

    public $rulesName = 'base';

    public $allowGuest = false;

    public $rules = [
        [
            'allow' => true,
            'roles' => ['@'],
        ],
    ];

    public $rootClass = '';

    /**
     * @var string the name of the error when the exception name cannot be determined.
     *             Defaults to "Error".
     */
    public $defaultName;
    /**
     * @var string the message to be displayed when the exception message contains sensitive information.
     *             Defaults to "An internal server error occurred.".
     */
    public $defaultMessage;


    public function getModel()
    {
        return false;
    }

    public function getSearchModel()
    {
        return false;
    }

    /**
     * Lists all Catalog models.
     * @return mixed
     */
    public function actionIndex($ajax = null)
    {
        $classSearchModel = $this->getSearchModel();
        $searchModel      = new $classSearchModel();

        $searchParams = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($searchParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Catalog model.
     * @param  integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id, $ref = false)
    {
        /** @var \BoltSystem\Yii2Logs\log\base\model\BaseModel */
        $model = $this->findModel($id);

        if ($this->getModel() !== LogDb::class) {
            Yii::$app->Log::createByClassName($this->getModel(), User::getRealCurrentUserID(), Yii::$app->Log::ACTION_DELETE, $model->id, $model->attributes);
        }

        if ($model->canDelete()) {
            $model->delete();
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return [
                'status' => 'OK',
            ];
        } else {
            if ($ref !== false) {
                return $this->redirect(html_entity_decode($ref));
            }

            return $this->redirect(['index']);
        }
    }

    protected function findModel($id)
    {
        $classModel = $this->getModel();

        $query = $classModel::find()->where(['id' => $id]);

        $query->undeleted();

        $model = $query->one();

        if ($model !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}