<?php
namespace common\components\behaviors;

use yii\base\Behavior;

//поведение для категорий и услуг. Для работы с логотипами
/**
 * Class ImgBehavior
 * @package common\components\behaviors
 *
 * @property  \common\models\Services|\common\models\Categories| \api\models\admin\AdminSlider | \api\models\admin\AdminRecommendations  $owner
 */
class ImgBehavior extends Behavior
{
	const IMG_DEFAULT = 'default';
	const IMG_MOBILE  = 'mobile';

	//путь до папки data в файловой системе
	private $dataPath = null;

	public static $ext = ['png', 'gif', 'jpg', 'jpeg'];

	public static $images = [
		'default' => ['path' => '', 'maxWidth' => 90, 'maxHeight' => 60, 'label' => 'Прямоугольная иконка'],
		'mobile'  => ['path' => 'mobile/', 'maxWidth' => 90, 'maxHeight' => 90, 'label' => 'Круглая иконка']
	];

	/**
	 * @return array
	 */
	public static function getRules()
	{
		$rules = [];

		foreach (self::$images as $key => $prop) {
			$rules[] = [$key, 'file', 'extensions' => self::$ext, 'maxFiles' => 1];
			$rules[] = [$key, 'image', 'maxWidth' => self::$images[$key]['maxWidth'], 'maxHeight' => self::$images[$key]['maxHeight']];
		}

		return $rules;
	}

	public static function getLabels()
	{
		$labels = [];

		foreach (self::$images as $key => $prop) {
			$labels[$key] = $prop['label'];
		}

		return $labels;
	}

	public function init()
	{
		parent::init();
		$this->dataPath = \Yii::$app->params['dataPath'];
	}

	public function getImgPath($folder = ImgBehavior::IMG_DEFAULT)
	{
		$pathPart = $this->getPathPart($folder);
		return $pathPart ? $this->dataPath . $pathPart : false;
	}

	public function getSrc($folder = ImgBehavior::IMG_DEFAULT)
	{
		$pathPart = $this->getPathPart($folder);
		return $pathPart ? '/data/' . $pathPart : false;
	}

	public function hasImg($folder = ImgBehavior::IMG_DEFAULT)
	{
		return (bool) $this->getPathPart($folder);
	}

	public function getFolder($folder = ImgBehavior::IMG_DEFAULT)
	{
		return $this->dataPath . $this->getFolderPart($folder);
	}

	public function getExtensions()
	{
		return self::$ext;
	}

	//часть пути, которая совпадает в файловой системе и в url вместе с именем файла
	private function getPathPart($folder)
	{
		$fileName = $this->getFileName($folder, $this->owner->getUkey());
		if ($fileName) {
			return $this->getFolderPart($folder) . $fileName;
		}

		return false;
	}

	//часть пути, которая совпадает в файловой системе и в url
	private function getFolderPart($folder)
	{
		return 'img/' . $this->owner->tableName() . '/' . self::$images[$folder]['path'];
	}

	/**
	 * @param $id string
	 * @param $folder string
	 * @return bool|string имя файла(с расширением) если он существует
	 */
	private function getFileName($folder, $id)
	{
		foreach (self::$ext as $ext) {
			$filePath = $this->getFolder($folder) . $id . '.' . $ext;
			if (file_exists($filePath)) {
				return $id . '.' . $ext;
			}
		}
		return false;
	}

    public function setFileFromBase64($folder = ImgBehavior::IMG_DEFAULT)
    {
        if ($this->unlinkModelFiles($folder) && $this->owner->uploadImage) {
            list($mime, $data) = explode(',', $this->owner->uploadImage);
            $extension = substr($mime, 11, -7);
            $file = $this->getFolder($folder) . $this->owner->getUkey() . '.' . $extension;
            $ifp = fopen($file, "wb");
            fwrite($ifp, base64_decode($data));
            fclose($ifp);
        }
        return true;
    }

    public function unlinkModelFiles($folder = ImgBehavior::IMG_DEFAULT)
    {
        $prevIcon = $this->getImgPath($folder);
        if (file_exists($prevIcon)) {
            unlink($prevIcon);
            $this->unlinkModelFiles($folder);
        }
        return true;
    }

    public function getBase64file($folder = ImgBehavior::IMG_DEFAULT)
    {
        $image = $this->getImgPath($folder);
        if ($image) {
            $imageSize = getimagesize($image);
            return "data:{$imageSize['mime']};base64," . base64_encode(file_get_contents($image, 1));
        }
        return null;
    }

}