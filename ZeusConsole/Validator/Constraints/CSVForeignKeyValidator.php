<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/17
 * Time: 下午4:30
 */

namespace ZeusConsole\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CSVForeignKeyValidator extends ConstraintValidator
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
        if (!$constraint instanceof CSVForeignKey) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CSVForeignKey');
        }
        if (null === $value) {
            return;
        }


        $foreignCSVDatas = $constraint->foreignCSVData;
        foreach ($foreignCSVDatas as $foreignCSVData) {

            if (isset($foreignCSVData[$constraint->foreignKey]) &&
                $foreignCSVData[$constraint->foreignKey] === $value
            ) {
                return;
            }

        }


        if ($this->context instanceof ExecutionContextInterface) {
            $this->context->buildViolation($constraint->message)
                ->setParameters([
                    '%value%' => $value,
                    '%cvs%' => $constraint->csv,
                    '%foreignKey%' => $constraint->foreignKey,
                ])
                ->addViolation();

        }

    }

}