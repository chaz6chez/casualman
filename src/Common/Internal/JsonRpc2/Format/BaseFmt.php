<?php
namespace CasualMan\Common\Internal\JsonRpc2\Format;

use Structure\Struct;

class BaseFmt extends Struct {
    protected $_special_error = null;
    protected $_special_code  = null;

    /**
     * 有致命错误
     * @return bool
     */
    public function hasFatalError(){
        if(boolval(!$this->_special_error)){
            return boolval(mb_strrpos($this->_special_error, '0x') !== false);
        }
        return false;
    }

    /**
     * 有特殊错误
     * @return bool
     */
    public function hasSpecialError() {
        return boolval($this->_special_error);
    }

    /**
     * 获取特殊错误
     * @return string|null
     */
    public function getSpecialError() {
        return $this->_special_error ? $this->_special_error : null;
    }

    /**
     * @param $error
     * @return mixed
     */
    public function setSpecialError($error) {
        return $this->_special_error = $error;
    }

    /**
     * 获取特殊错误码
     * @return string|null
     */
    public function getSpecialCode() {
        return $this->_special_code ? array_values($this->_special_code)[0] : null;
    }

    /**
     * 设置特殊code
     * @param $code
     * @return mixed
     */
    public function setSpecialCode($code) {
        return $this->_special_code = $code;
    }
}