<?php

namespace app\DTO;

readonly class BookingData
{
    public function __construct(
        public int $carId,
        public int $slotId,
        public string $customerName,
        public string $customerPhone
    ) {}

    // Метод для создания DTO из массива данных (например, из Request)
    public static function fromRequest(array $data): self
    {
        return new self(
            carId: (int) $data['car_id'],
            slotId: (int) $data['slot_id'],
            customerName: $data['customer_name'],
            customerPhone: $data['customer_phone']
        );
    }
}
