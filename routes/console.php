<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('control-plane:about', function () { $this->info('MyCalculator Laravel Control Plane'); });
