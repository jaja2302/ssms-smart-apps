<?php

namespace App\Services;

class DataServiceImportRealisasi
{
    protected $data = [];

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
