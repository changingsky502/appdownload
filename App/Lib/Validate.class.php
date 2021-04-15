<?php



class Validate {

    private $rule;
    private $error;

    public function __construct($rule) {
        $this->rule = $rule;
    }

    public function check($data) {
        foreach ($this->rule as $key=>$value) {
            $rules = explode('|', $value);
            foreach ($rules as $rule) {
                if ($rule=='require') {
                    if (!isset($data['key'])) {
                        $this->error[$key] = '';
                    }
                }
            }

        }
    }

    /**
     * 验证数据最大长度
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则
     * @return bool
     */
    protected function max($value, $rule) {
        $length = mb_strlen((string) $value);
        return $length <= $rule;
    }

}