<?php

namespace Picqer\Barcode\Types;

interface TypeInterface
{
    public function getCode($code);
    public function getWidth($code);
    public function getHeight($code);
    public function getLabel($code);
}
