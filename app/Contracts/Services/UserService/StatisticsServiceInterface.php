<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;


interface StatisticsServiceInterface
{


    public function getPieCharts($month, $year);
    public function causeRenalFailure();
    public function getCenterStatistics();




}