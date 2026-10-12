<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = '<html><body><h2>Pengiriman & pembayaran</h2></body></html>';
$response = new Illuminate\Testing\TestResponse(new Illuminate\Http\Response($html));
try {
    $response->assertSee('Pengiriman & pembayaran');
    echo "ASSERT_OK\n";
} catch (Throwable $e) {
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
}
