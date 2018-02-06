<?php
/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2017/3/7
 * Time: 下午4:33
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate;


class VueIndexGenerate
{
    private $files = [];

    function getImports()
    {
        $imports = [];
        foreach ($this->files as $f) {
            $imports[] = "import { " . $f . ", " . $f . "Method } from './" . $f . "';";
        }
        return $imports;
    }

    function getImportsErrorCode()
    {
        $imports = [];

        foreach ($this->files as $f) {
            $imports[] = "import { default as Error" . $f . " } from './Error" . $f . "';";
        }
        return $imports;
    }

    function getNames()
    {
        $idx = 0;
        $count = count($this->files);
        $names = [];

        foreach ($this->files as $f) {
            $suffix = ',';
            $idx++;
            if ($idx == $count) {
                $suffix = '';
            }
            $names[] = '  ' . $f . '.' . $f . $suffix;
        }
        return $names;
    }

    function getErrorCodeMap()
    {
        $errors = [];
        foreach ($this->files as $f) {
            $errors[] = "CodesMap[Error" . $f . ".method] = Error" . $f . ".codes;";
        }
        return $errors;
    }

    function setFiles($files)
    {
        $this->files = $files;
    }
}