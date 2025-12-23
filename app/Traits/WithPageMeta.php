<?php

namespace App\Traits;

trait WithPageMeta
{
    public array $breadcrumbs = [];
    public string $title = '';
    public string $description = '';

    protected function setPageMeta(string $title, string $description = '', array $breadcrumbs = []): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->breadcrumbs = $breadcrumbs;
    }
}
