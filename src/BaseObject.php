<?php

namespace Jormin\Pay;


/**
 * Class BaseObject
 * @package Jormin\Qiniu
 */
class BaseObject
{

    /**
     * 失败
     *
     * @param $message
     * @param null $data
     * @return array
     */
    public function error($message, $data = null)
    {
        is_object($data) && $data = (array)$data;
        $return = ['success' => false, 'message' => $message, 'data' => $data];
        return $return;
    }

    /**
     * 成功
     *
     * @param $message
     * @param null $data
     * @return array
     */
    public function success($message, $data = null)
    {
        is_object($data) && $data = (array)$data;
        $return = ['success' => true, 'message' => $message, 'data' => $data];
        return $return;
    }
}
