<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/17
 * Time: 下午8:02
 */

namespace ZeusConsole\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CSVUniqueValidator
 * @package ZeusConsole\Validator\Constraints
 */
class CSVUniqueValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CSVUnique) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CSVUnique');
        }
        if (is_null($value)) {
            return;
        }


        $CSVDatas = $constraint->CSVDatas;
        $valueCount = 0;
        foreach ($CSVDatas as $CSVData) {
            if (isset($CSVData[$constraint->columnName]) &&
                $CSVData[$constraint->columnName] === $value
            ) {
                $valueCount++;
            }
        }

        if ($valueCount <= 1) {
            return;
        }


        if ($this->context instanceof ExecutionContextInterface) {
            $this->context->buildViolation($constraint->message)
                ->setParameters([
                    '%value%' => $value,
                ])
                ->addViolation();
        }
    }


}