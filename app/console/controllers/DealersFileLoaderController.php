<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\Dealers;
use common\models\Users;
use common\models\DealersEmployees;
use yii\rbac\DbManager;

class DealersFileLoaderController extends Controller
{
    /**
     * Добавление салонов и их сотрудников
     * @param $folder
     * @return bool|int
     */
    public function actionAddDealers($folder)
    {
        $dealers = 'workerz.csv';
        $dealers = $folder . $dealers;

        $dealersPasswords = fopen($folder . 'DealersPassword.csv', 'w');
        $header = [
            'адрес офиса',
            'логин',
            'пароль'
        ];

        \fputcsv($dealersPasswords, $header);

        if (file_exists($dealers)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $handle = fopen($dealers, 'r');
                $i = 375999990001;
                while (($buffer = fgets($handle)) !== false) {
                    $array = explode(',', $buffer);

                    list($region, $head, $name, $position, , $address) = $this->removeQuotes($array);

                    // Добавим Дилера
                    if(!$new_dealer = Dealers::getDealerByAddress($address)) {
                        $new_dealer = new Dealers();
                        $new_dealer->head = $head;
                        $new_dealer->address = $address;
                        $new_dealer->region = $region;
                        $new_dealer->save();
                    }

                    // Добавим Cотрудников
                    $password = $this->generatePassword();
                    $phone = $i++;

                    $new_employee = new DealersEmployees();
                    $new_employee->name = $name;
                    $new_employee->dealer_id = $new_dealer->id;
                    $new_employee->login = $phone;
                    $new_employee->position = $position;
                    $new_employee->save();

                    // Добавим Юзера
                    $new_user = new Users();
                    $new_user->phone = (string)$phone;
                    $new_user->setPassword($password);

                    if($new_user->save()) {
                        //Присвоим права
                        $auth = new DbManager;
                        $auth->init();
                        $role = $auth->getRole('promo-manager');
                        $auth->assign($role, $new_user->user_id);
                    }

                    \fputcsv($dealersPasswords, [
                        $name,
                        $phone,
                        $password,
                    ]);
                    return 0;
                }
                $transaction->commit();
                fclose($dealersPasswords);
                fclose($handle);

            } catch (\Exception $e) {
               // $transaction->rollBack();
                print $e->getMessage();
                return false;
            }
        }

        echo "-------Done--------\n";
        return 0;
    }

    public function removeQuotes($array)
    {
        foreach ($array as $k => $val) {
            $array[$k] = str_replace('"', '', $val);
        }
        return $array;
    }

    public function generatePassword()
    {
        $password = '';
        $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
        $max = 10;
        while ($max--) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
}