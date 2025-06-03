<?php

namespace Picqer\Barcode\Types;

class TypeCode128 implements TypeInterface
{
    public function getCode($code)
    {
        return $code;
    }

    public function getWidth($code)
    {
        return strlen($code) * 2;
    }

    public function getHeight($code)
    {
        return 30;
    }

    public function getLabel($code)
    {
        return $code;
    }
}
