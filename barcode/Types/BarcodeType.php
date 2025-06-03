<?php

namespace Picqer\Barcode\Types;

interface BarcodeType
{
    public function getCode($code);

    public function getWidth($code);

    public function getHeight($code);

    public function getLabel($code);
}
