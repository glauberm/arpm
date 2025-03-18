<?php

namespace Tests\Feature;

use App\Services\SpreadsheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;

    private SpreadsheetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SpreadsheetService::class);
    }

    public function test_process_spreadsheet(): void
    {
        Queue::fake();

        $filePath = Storage::disk('local')->get('tests/spreadsheet.csv');

        $this->service->processSpreadsheet($filePath);

        $this->assertDatabaseCount('products', 10);

        Queue::assertPushed(ProcessProductImage::class);
    }

    public function test_process_spreadsheet_with_errors(): void
    {
        Queue::fake();

        $filePath = Storage::disk('local')->get('tests/spreadsheet-with-errors.csv');

        $this->service->processSpreadsheet($filePath);

        $this->assertDatabaseEmpty('products');

        Queue::assertNotPushed(ProcessProductImage::class);
    }
}
