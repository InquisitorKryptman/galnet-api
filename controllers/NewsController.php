<?php

namespace app\controllers;

use app\components\ResponseBuilder;

use app\models\News;

use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\Cors;

use Yii;

class NewsController extends \yii\web\Controller
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
	            ],
	        ],
	        'corsFilter' => [
	            'class' => Cors::className(),
	            'cors' => [
	           		'Origin' => ['*'],
	           		'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS', 'PUT', 'HEAD']
	            ]
	        ],
	    ];
	}

	/**
	 * Paginated endpoint for display all Galnet News
	 * @return array
	 */
	public function actionIndex()
	{
		$query = News::find()
			->orderBy('published_at_native desc');

		return ResponseBuilder::build($query);
	}

	/**
	 * RSS Feed of the data
	 */
	public function actionRss()
	{
	    $dataProvider = new ActiveDataProvider([
	        'query' => News::find()->orderBy('published_at_native desc'),
	        'pagination' => [
	            'pageSize' => 20
	        ],
	    ]);

	    $response = Yii::$app->getResponse();
	    $headers = $response->getHeaders();

	    // Send the xml-rss headers
	    $headers->set('Content-Type', 'text/xml; charset=utf-8');
	    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

	    echo \Zelenin\yii\extensions\Rss\RssView::widget([
	        'dataProvider' => $dataProvider,
	        'channel' => [
	            'title' => 'Galnet News Network API',
	            'link' => Url::toRoute('/', true),
	            'description' => 'Galnet News Network API'
	        ],
	        'items' => [
	            'title' => function ($model, $widget) {
                    return $model->title;
                },
	            'description' => function ($model, $widget) {
                    return $model->content;
                },
	            'link' => function ($model, $widget) {
                    return Yii::$app->params['galnet']['url'] . '/galnet/uid/' . $model->uid;
                },
	            'author' => function ($model, $widget) {
                    return 'Galnet';
                },
	            'guid' => function ($model, $widget) {
                    return $model->uid;
                },
	            'pubDate' => function ($model, $widget) {
                    $date = new \DateTime;
                    $date->setTimestamp($model->published_at);
                    return $date->format(DATE_RSS);
                }
	        ]
	    ]);
	}
}