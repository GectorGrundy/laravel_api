<?php
declare(strict_types=1);

namespace App\Support;

use App\Http\GoDataContainer;
use App\Models\Domain;
use View;

class LandingFileCompiler
{
    private $domain;

    public function compile(Domain $domain, string $path, array $view_data = [])
    {
        $this->domain = $domain;

        $this->prepareForCustomViewPath();

        $fileinfo = pathinfo($path);

        try {
            return view($fileinfo['filename'], $view_data)->render();
        } catch (\InvalidArgumentException $e) {
            /**
             * @var GoDataContainer $data_container
             */
            $data_container = app(GoDataContainer::class);

            hardRedirect($data_container->getCurrentDomain()['host'] . '?badlanding');
        }
    }

    /**
     * Добавление возможности обработки blade шаблонов лендингов
     */
    private function prepareForCustomViewPath()
    {
        View::addExtension('html', 'blade');
        View::addNamespace('Custom', $this->domain->realpath);
        View::addLocation($this->domain->realpath);
    }
}