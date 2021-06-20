<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slots extends Model
{
    use HasFactory;
    protected $table = 'slots';

    protected $fillable = ["id", "block", "block_seat_number", "is_near_to_lift", "is_reserved_seat", "booking_holds_upto","seat_status", "is_booked", "created_at", "updated_at"
    ];
}
