<?php

namespace console\controllers;

use console\models\CategoriesAndServices;
use PbrLibBelCommon\Caller\Downloader;
use Yii;
use console\models\Locations;
use console\models\Services;
use console\models\Categories;
use console\models\ServicesCount;

/**
 * Class EripRefreshController
 * @package console\controllers
 */
class EripRefreshController extends AbstractCronTask
{
	protected $folder;

	public function init()
	{
		ini_set('memory_limit','1024M');
		Yii::beginProfile('Refreshing locations and services.');
		parent::init();
	}

	public function __destruct()
	{
		if (isset($this->folder) && is_dir($this->folder)) {
			array_map('unlink', glob($this->folder . '/*.*'));
			rmdir($this->folder);
		}
	}

    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function handler(array $params)
	{
		$start = microtime(true);
		$transaction = Yii::$app->getDb()->beginTransaction();
		Yii::$app->getDb()->createCommand('SET CONSTRAINTS ALL DEFERRED')->execute();

		try {
			//update всех локаций
			$countLocations = Locations::refreshLocations($this->getLocations());
			//обновляем услуги и категории. Они приходят скопом в одном дереве
			$refreshResults = CategoriesAndServices::refresh($this->getCategoriesAndServices());

			Yii::info('Refreshing success');

			$transaction->commit();
			Yii::$app->cache->invalidateTags([Categories::tableName(), Services::tableName(), ServicesCount::tableName(), Locations::tableName()]);
			Yii::endProfile(
				'Refreshing locations and services end. ' .
				'Time: ' . number_format(microtime(true) - $start, 3, ',', ' ') . 'sec. ' .
				'CountLocations: ' . number_format($countLocations, 0, ',', ' ') . ' ' .
				'CountCategories: ' . number_format($refreshResults['categories'], 0, ',', ' ') . ' ' .
				'CountCategoriesUpdate: ' . number_format($refreshResults['countCategoriesUpdate'], 0, ',', ' ') . ' ' .
				'CountCategoriesInsert: ' . number_format($refreshResults['countCategoriesInsert'], 0, ',', ' ') . ' ' .
				'CountCategoriesRemoved: ' . number_format($refreshResults['countCategoriesRemoved'], 0, ',', ' ') . ' ' .
				'CountServices: ' . number_format($refreshResults['services'], 0, ',', ' ') . ' ' .
				'CountServicesUpdate: ' . number_format($refreshResults['countServicesUpdate'], 0, ',', ' ') . ' ' .
				'CountServicesInsert: ' . number_format($refreshResults['countServicesInsert'], 0, ',', ' ') . ' ' .
				'CountServicesRemoved: ' . number_format($refreshResults['countServicesRemoved'], 0, ',', ' ') . ' '
			);
		} catch (\Exception $e) {
			$transaction->rollBack();
			Yii::endProfile('Refreshing locations and services end with error. ' . $e->getMessage());
			throw $e;
		}
	}

	private function getLocations()
	{
		$json = file_get_contents(ERIP_API_URL_TREE . '/geo.php');
		return json_decode($json, true);
	}

	/**
	 * @return array в этом массиве содержатся одновременно и категории, и услуги
	 */
	private function getCategoriesAndServices()
	{
		$json = $this->loadZipFile(ERIP_API_URL_TREE . '/erip.zip');
		return json_decode($json, true);
	}

	private function loadZipFile($url)
	{
		$this->folder = Yii::getAlias('@runtime') . '/data/erip-data';
		if (!file_exists($this->folder) || !is_dir($this->folder)) {
			mkdir($this->folder, 0775, true);
		}
		$file = $this->folder . '/json.zip';

        $downloader = new Downloader($url);
		$res = fopen($file, 'w+');
        $downloader->saveToResource($res);
        fclose($res);

		$zip = new \ZipArchive;
		$res = $zip->open($file);
		if ($res === true) {
			if (!$zip->extractTo($this->folder)) {
				throw new \Exception('Failed to extract zip archive');
			}
			$zip->close();
		} else {
			throw new \Exception('Failed to open zip archive');
		}

		$result = file_get_contents($this->folder . '/erip.json');

		if (!$result) {
			throw new \Exception('Failed to unzip data');
		}
		return $result;
	}
}
