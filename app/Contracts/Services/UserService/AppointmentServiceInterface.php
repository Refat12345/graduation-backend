<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;

interface AppointmentServiceInterface 
{
  //  public function addAppointment(array $data);
    public function getAppointmentsByCenter($centerId);
    public function getUserAppointments($userId);
    public function getAppointmentsByCenterAndDate($centerId, $year, $month, $day);
    public function populateAppointments($centerId);
    public function assignAppointmentToUser($appointmentId, $userId);
    public function swapAppointmentsBetweenUsers($appointmentId1, $appointmentId2);
    public function updateAppointmentsStatus($centerId);

}
