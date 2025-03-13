<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use League\Csv\Reader;

class AddProductMasterDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-product-master-data-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = public_path('assets/csv/master-products.csv');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }

        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // First row of files is header

        foreach ($csv as $record) {
            Product::updateOrCreate(
                ['product_code' => $record['商品コード']],
                [
                    'name' => $record['商品名'],
                    'product_type' => $record['規格'],
                ]
            );
        }

        $this->info('Import completed!');
    }
}
