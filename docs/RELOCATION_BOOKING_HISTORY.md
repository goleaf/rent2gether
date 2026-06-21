# Relocation Booking History

Relocation history must show where the guest actually lived over time.

## Do Not Overwrite

Do not overwrite `sleeping_place_id` on the original booking. That would erase where the guest lived before relocation and corrupt calendar, deposit, review, and complaint evidence.

## MVP History Model

Original booking:

```text
10 July - 15 July
SleepingPlace A
```

New relocation segment:

```text
15 July - 20 July
SleepingPlace B
relocation_from_booking_id = original booking id
booking_type = relocation
```

`booking_relocations.original_booking_id` links the process to the first booking, and `booking_relocations.new_booking_id` links the created segment after apply.

## Review and Complaint Context

Reviews, complaints, deposit checks, and damage evidence must be able to refer to the correct old or new `SleepingPlace`.

