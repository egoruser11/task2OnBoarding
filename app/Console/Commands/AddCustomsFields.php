<?php

namespace App\Console\Commands;

use App\Http\Services\kommoService;
use Illuminate\Console\Command;

class AddCustomsFields extends Command
{
    public function __construct(private kommoService $kommoService)
    {
        parent::__construct();
    }
    protected $signature = 'app:add-custom-fields';
    protected $description = 'Command add custom fields';

    public function handle()
    {
        try {
            if (!$this->kommoService->fieldExists('Male','contacts')) {
                $result = $this->kommoService->createCustomFieldMaleForContacts();
                $this->info($result);
            }
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return self::FAILURE;
        }
        try {
            if (!$this->kommoService->fieldExists('Age','contacts')) {
                $result = $this->kommoService->createCustomFieldAgeForContacts();
                $this->info($result);
            }
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return self::FAILURE;
        }
        try {
            if (!$this->kommoService->fieldExists('Date of end lead','leads')) {
                $result = $this->kommoService->createLeadCloseDateTimeField();
               // $this->info($result);
            }
            $this->info("Поля добавлены");
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
