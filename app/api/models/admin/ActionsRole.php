<?php

namespace api\models\admin;

use common\models\AbstractModel;

/**
 * @property string $controller
 * @property array $actions
 * @property string $role_name
 */
class ActionsRole extends AbstractModel implements \JsonSerializable
{

    public function jsonSerialize()
    {
        return $this->getAttributes();
    }
}

