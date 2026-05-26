<?php

declare(strict_types=1);

require_once __DIR__ . '/../Controllers/AuthController.php';


abstract class ClassDiagramUser
{
    public function __construct(
        public string $userID,
        public string $name,
        public string $email,
    ) {
    }
}

final class ClassDiagramDriver extends ClassDiagramUser
{
    public function __construct(
        string $userID,
        string $name,
        string $email,
        public string $licenseNumber,
        public int $loyaltyPoints,
        public string $phoneNumber,
    ) {
        parent::__construct($userID, $name, $email);
    }

    
    public function register(array $postData): array
    {
        return AuthController::register($postData);
    }

    
    public function login(array $postData): array
    {
        return AuthController::login($postData);
    }

    public function updateProfile(): void
    {
        
    }

    public function viewReservations(): void
    {
        DriverController::bookings();
    }

    public function makePayment(): void
    {
        
    }
}

final class ClassDiagramAdmin extends ClassDiagramUser
{
    public function __construct(string $userID, string $name, string $email)
    {
        parent::__construct($userID, $name, $email);
    }

    public function manageUsers(): void
    {
        
    }

    public function manageParkingSpots(): void
    {
        
    }

    public function viewReports(): void
    {
        
    }

    public function updateConfig(): void
    {
        
    }
}

final class ClassDiagramParkingAttendant extends ClassDiagramUser
{
    public function __construct(
        string $userID,
        string $name,
        string $email,
        public string $attendantID,
    ) {
        parent::__construct($userID, $name, $email);
    }

    public function verifyReservation(): void
    {
        
    }

    public function updateSpotStatus(): void
    {
        
    }
}

final class ClassDiagramLawEnforcementOfficer extends ClassDiagramUser
{
    public function __construct(
        string $userID,
        string $name,
        string $email,
        public string $officerID,
        public string $badgeNumber,
    ) {
        parent::__construct($userID, $name, $email);
    }

    public function issueFine(): void
    {
        OfficerController::violation();
    }

    public function verifyVehicle(): void
    {
        
    }
}

final class ClassDiagramParkingSpot
{
    public function __construct(
        public string $spotID,
        public string $location,
        public string $type,
        public string $status,
        public float $hourlyRate,
    ) {
    }

    public function updateStatus(): void
    {
        
    }

    public function getDetails(): void
    {
        
    }
}

final class ClassDiagramReservation
{
    public function __construct(
        public string $reservationID,
        public string $userID,
        public string $spotID,
        public string $startTime,
        public string $endTime,
        public string $status,
        public float $totalAmount,
    ) {
    }

    public function createReservation(): void
    {
        DriverController::book();
    }

    public function cancelReservation(): void
    {
        
    }

    public function extendReservation(): void
    {
        
    }

    public function calculateTotal(): void
    {
        
    }
}

final class ClassDiagramVehicleProfile
{
    public function __construct(
        public string $vehicleID,
        public string $licensePlate,
        public string $make,
        public string $model,
        public string $color,
    ) {
    }

    public function addVehicle(): void
    {
        DriverController::vehicles();
    }

    public function updateVehicle(): void
    {
        DriverController::vehicles();
    }

    public function removeVehicle(): void
    {
        DriverController::vehicles();
    }
}

final class ClassDiagramPayment
{
    public function __construct(
        public string $paymentID,
        public string $reservationID,
        public float $amount,
        public string $paymentDate,
        public string $status,
    ) {
    }

    public function processPayment(): void
    {
        
    }

    public function refundPayment(): void
    {
        
    }

    public function getPaymentDetails(): void
    {
        
    }
}

final class ClassDiagramPaymentMethod
{
    public function __construct(
        public string $methodID,
        public string $paymentID,
        public string $type,
        public string $details,
    ) {
    }
}

final class ClassDiagramFine
{
    public function __construct(
        public string $fineID,
        public string $userID,
        public string $vehicleID,
        public float $amount,
        public string $reason,
        public string $issueDate,
        public string $status,
    ) {
    }

    public function issueFine(): void
    {
        OfficerController::violation();
    }

    public function payFine(): void
    {
        DriverController::fines();
    }

    public function getFineDetails(): void
    {
        DriverController::fines();
    }
}

final class ClassDiagramAuditLog
{
    public function __construct(
        public string $logID,
        public string $userID,
        public string $action,
        public string $timestamp,
    ) {
    }
}

final class ClassDiagramReview
{
    public function __construct(
        public string $reviewID,
        public string $userID,
        public string $spotID,
        public int $rating,
        public string $comment,
    ) {
    }
}

final class ClassDiagramDispute
{
    public function __construct(
        public string $disputeID,
        public string $paymentID,
        public string $reason,
        public string $status,
    ) {
    }
}

final class ClassDiagramLocation
{
    public function __construct(
        public string $locationID,
        public string $address,
        public string $coordinates,
    ) {
    }
}
