<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/17
 * Time: 下午4:26
 */

namespace ZeusConsole\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * Class CSVForeignKey
 * @package ZeusConsole\Validator\Constraints
 *
 */
class CSVForeignKey extends Constraint
{
    public $message = 'The value "%value%" not exist in %cvs%:%foreignKey%';

    /**
     * @var string 文件路径
     */
    public $csv;
    /**
     * @var string 外键key
     */
    public $foreignKey;

    /**
     * @var [] 外键数据
     */
    public $foreignCSVData;

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
        return ['csv', 'foreignKey', 'foreignCSVData'];
    }


}