<?php

namespace app\controllers;

use app\components\ResponseBuilder;
use app\models\Station;

use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\helpers\Inflector;

use Yii;

class StationsController extends \yii\rest\Controller
{
	/**
	 * @internal
	 * Only allows POST requests to the hook endpoints
	 */
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'index'  	=> ['get'],
					'view'  	=> ['get'],
				],
			],
			'corsFilter' => [
				'class' => Cors::className(),
				'cors' => [
			   		'Origin' => ['*'],
			   		'Access-Control-Request-Method' => ['GET', 'HEAD']
				]
			],
		];
	}

	/**
	 * Paginated endpoint for display all commodities from Eddb
	 * @return array
	 */
	public function actionIndex()
	{
		$model = new Station;
		$params['Station'] = Yii::$app->request->get();
		$query = $model->search($params);
		
		return ResponseBuilder::build($query, 'stations', Yii::$app->request->get('sort', 'name asc'));
	}

	/**
	 * Retrieve the details for a specific commodity
	 * @param integer $id
	 * @return array
	 */
	public function actionView($id=NULL)
	{
		if ($id === NULL)
			throw new HttpException(400, 'Missing ID parameter');

		$query = Station::find()->where(['id' => $id]);
		return ResponseBuilder::build($query, 'stations', 'id asc');
	}
}