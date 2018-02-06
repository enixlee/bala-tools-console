<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/17
 * Time: 下午7:58
 */

namespace ZeusConsole\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * 唯一性检测
 * Class CSVUnique
 * @package ZeusConsole\Validator\Constraints
 */
class CSVUnique extends Constraint
{
    public $message = 'The %value% duplicate';
    /**
     * 列名
     * @var
     */
    public $columnName;
    /**
     * csv数据
     * @var array
     */
    public $CSVDatas;


    /**
     * Returns the name of the class that validates this constraint.
     *
     * By default, this is the fully qualified name of the constraint class
     * suffixed with "Validator". You can override this method to change that
     * behaviour.
     *
     * @return string
     *
     * @api
     */
    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }

    /**
     * Returns the name of the required options.
     *
     * Override this method if you want to define required options.
     *
     * @return array
     *
     * @see __construct()
     *
     * @api
     */
    public function getRequiredOptions()
    {
        return ['columnName', 'CSVDatas'];
    }
}