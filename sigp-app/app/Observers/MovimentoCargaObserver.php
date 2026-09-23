<?php
// php: c:\xampp\htdocs\sigp\app\Observers\MovimentoCargaObserver.php
namespace App\Observers;

use App\Models\MovimentoCarga;
use App\Services\BillingService;

class MovimentoCargaObserver
{
    public function created(MovimentoCarga $mc)
    {
        BillingService::generateForMovimentoCarga($mc);
    }
}