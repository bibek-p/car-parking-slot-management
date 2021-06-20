<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Slots;
use App\Models\Bookings;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
date_default_timezone_set('Asia/Kolkata');



class BookingController extends Controller
{

    public function index()
    {
        return "N";
    }

    public function bookSlots(Request $request)
    {
        $errorresponse = array();
        $sussresponse = array();

        if ($request->user_id != "") {
            $booking_user_data = User::find($request->user_id);
            if (!empty($booking_user_data)) { //Cecking is booking user is valid user or not
                $this->freeAllHoldSlotToRebook(); //Making Free Hold slots
                if ($booking_user_data->user_type == 2) { //if theuser is come under special booking or differently-abled and pregnant women
                    $avliable_slot = Slots::where('is_reserved_seat', '=', 1)->where('seat_status', '=', 0)->get();
                    if ($avliable_slot->count() != 0) {
                        // if all reserved seats are filled then we are alloting from the genral booking slots
                        $avliable_slot = Slots::where('seat_status', '=', 0)->get();
                    } else {
                        //if the use is comes under genral user
                        $avliable_slot = Slots::where('seat_status', '=', 0)->where('is_reserved_seat', '=', 0)->get();
                    }
                } else {
                    //if the use is comes under genral user
                    $avliable_slot = Slots::where('seat_status', '=', 0)->where('is_reserved_seat', '=', 0)->get();
                }
                if ($avliable_slot->count() == 0) {
                    // Here We Dont have any slots now all are booked
                    $errorresponse["status"] = "false";
                    $errorresponse["message"] = "We Dont Have Slots Now. Please Book After Sometime";
                } else {
                    //If Slots Are Avaliable To Book
                    $slot_allocation_no = $avliable_slot[0]["id"]; //Allocating the 1st slot from avaliable slots
                    $bookings["user_id"] = $request->user_id;
                    $bookings["slot_id"] = $slot_allocation_no;

                    // Calculating waiting time
                    $count_total_noof_slots = Slots::all();
                    $count_total_noof_slots = $count_total_noof_slots->count();
                    $count_total_holdand_booked_slot = Slots::where('seat_status', '!=', 0)->get();
                    $count_total_holdand_booked_slot = $count_total_holdand_booked_slot->count();
                    $limit_per_cap = round(($count_total_noof_slots / 100) * env('LIMT_CAPING_PERCENTAGE_FOR_AVOID_WAITING_TIME'));
                    if ($limit_per_cap < $count_total_holdand_booked_slot) {
                        $minutes_to_add = env('HOLD_BOKING_TIME_IN_MIN');
                    } else {
                        $minutes_to_add = env('HOLD_BOKING_TIME_IN_MIN') + env('BEFORE_BOKING_TIME_IN_MIN');
                    }

                    $current_date_time = date('Y-m-d H:i:s');
                    $get_current_booking_time = new DateTime($current_date_time);
                    $get_current_booking_time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
                    $booking_holds_upto = $get_current_booking_time->format('Y-m-d H:i:s');



                    $save_booking = Bookings::create($bookings);
                    if ($save_booking) {

                        $booking_slot_data = Slots::find($slot_allocation_no);
                        $booking_slot_data_status["seat_status"] = 1;
                        $booking_slot_data_status["booking_holds_upto"] = $booking_holds_upto;
                        $f_bookings = $booking_slot_data->update($booking_slot_data_status);

                        if ($f_bookings) {
                            $sussresponse["status"] = true;
                            $sussresponse["message"] = "Booking Sucessfull";
                            $sussresponse["seatno"] = $avliable_slot[0]["block"] . $avliable_slot[0]["block_seat_number"];
                            $currenttime = date('h:i:s');
                            $endTime = strtotime("+" . $minutes_to_add . " minutes", strtotime($currenttime));
                            $sussresponse["exp_time"] = date('h:i:s', $endTime);
                        } else {
                            $errorresponse["status"] = "false";
                            $errorresponse["message"] = "Got Somethings Wrong While Updating Slots";
                        }
                    } else {
                        $errorresponse["status"] = "false";
                        $errorresponse["message"] = "Got Somethings Wrong While Bookings";
                    }
                }
            } else {
                $errorresponse["status"] = "false";
                $errorresponse["message"] = "Invalid User Id.";
            }
        } else {
            $errorresponse["status"] = "false";
            $errorresponse["message"] = "User id could not blank";
        }

        if (empty($errorresponse)) {
            return $sussresponse;
        } else {
            return $errorresponse;
        }
    }

    public function freeAllHoldSlotToRebook()
    {



        $hold_slots = Bookings::where('is_user_car_parked', '=', 0)->get();
        foreach ($hold_slots as $i => $hold_slot) {
            $get_current_booking_slot = $hold_slot["slot_id"];
            $get_hold_upto_time = Slots::where('id', '=', $get_current_booking_slot)->first();


            $current_date_time = date('Y-m-d H:i:s');
            $boking_hold_upto = $get_hold_upto_time["booking_holds_upto"];

            if ($boking_hold_upto < $current_date_time) {
                //checking waiting time of slot and making it free
                $booking_slot_data = Slots::find($get_current_booking_slot);
                $booking_slot_data_status["seat_status"] = 0;
                $booking_slot_data_status["booking_holds_upto"] = "";
                $booking_slot_data->update($booking_slot_data_status);

                $get_booking_auto_id = $hold_slot["id"];
                $booking_data = Bookings::find($get_booking_auto_id);
                $booking_data_status["is_user_car_parked"] = -1;
                $booking_data->update($booking_data_status);
            }
        }
    }


    public function slotCheckin(Request $request)
    {
        $errorresponse = array();
        $sussresponse = array();

        if ($request->user_id != "") {
            if ($request->bookingid != "") {
                $booking_details = Bookings::where('id', '=', $request->bookingid)->where('user_id', '=', $request->user_id)->first();
                if (!$booking_details) {
                    $errorresponse["status"] = "false";
                    $errorresponse["message"] = "Invalid Booking ID Or Invalid User ID";
                } else {
                    echo "bibek";
                    if ($booking_details["is_user_car_parked"] == 0) {
                        $slot_id = $booking_details['slot_id'];

                        $booking = Bookings::find($request->bookingid);
                        $booking_details_update["is_user_car_parked"] = 1;
                        $booking->update($booking_details_update);

                        $booking_slot_data = Slots::find($slot_id);
                        $booking_slot_data_status["seat_status"] = 2;
                        $booking_slot_data->update($booking_slot_data_status);
                        $sussresponse["status"] = "true";
                        $sussresponse["message"] = "Slot Successfully Checked In";
                    } elseif ($booking_details["is_user_car_parked"] == -1) {
                        $errorresponse["status"] = "false";
                        $errorresponse["message"] = "Failed ! Slot time is overed.";
                    } elseif ($booking_details["is_user_car_parked"] == 1) {
                        $errorresponse["status"] = "false";
                        $errorresponse["message"] = "Failed ! Slot Already Checkedin.";
                    } else {
                        $errorresponse["status"] = "false";
                        $errorresponse["message"] = "Failed ! Slot Already Checkedout.";
                    }
                }
            } else {
                $errorresponse["status"] = "false";
                $errorresponse["message"] = "Booking Id Could Not Be Blank";
            }
        } else {
            $errorresponse["status"] = "false";
            $errorresponse["message"] = "User Id Could Not Be Blank";
        }


        if (empty($errorresponse)) {
            return $sussresponse;
        } else {
            return $errorresponse;
        }
    }

    public function slotCheckout(Request $request)
    {
        $errorresponse = array();
        $sussresponse = array();

        if ($request->user_id != "") {
            if ($request->bookingid != "") {
                $booking_details = Bookings::where('id', '=', $request->bookingid)->where('user_id', '=', $request->user_id)->first();
                if (!$booking_details) {
                    $errorresponse["status"] = "false";
                    $errorresponse["message"] = "Invalid Booking ID Or Invalid User ID";
                } else {
                    if ($booking_details["is_user_car_parked"] == 1) {
                        $slot_id = $booking_details['slot_id'];

                        $booking = Bookings::find($request->bookingid);
                        $booking_details_update["is_user_car_parked"] = 2;
                        $booking->update($booking_details_update);

                        $booking_slot_data = Slots::find($slot_id);
                        $booking_slot_data_status["seat_status"] = 0;
                        $booking_slot_data->update($booking_slot_data_status);
                        $sussresponse["status"] = "true";
                        $sussresponse["message"] = "Slot Successfully Checked Out";
                    } elseif ($booking_details["is_user_car_parked"] == 2) {
                        $sussresponse["status"] = "false";
                        $sussresponse["message"] = "Faild ! Slot Already Checked Out";
                    } else {
                        $errorresponse["status"] = "false";
                        $errorresponse["message"] = "Failed ! Slot Did Not Checkedin Yet.";
                    }
                }
            } else {
                $errorresponse["status"] = "false";
                $errorresponse["message"] = "Booking Id Could Not Be Blank";
            }
        } else {
            $errorresponse["status"] = "false";
            $errorresponse["message"] = "User Id Could Not Be Blank";
        }


        if (empty($errorresponse)) {
            return $sussresponse;
        } else {
            return $errorresponse;
        }
    }

    public function getAllAvailableSlot(Request $request)
    {
        $avliable_slot = Slots::where('seat_status', '=', 0)->get();
        return $avliable_slot;
    }
    public function getAlloccupiedSlot(Request $request)
    {
        $avliable_slot = Slots::where('seat_status', '!=', 0)->get();
        return $avliable_slot;
    }

    public function getAllUsers(Request $request)
    {
        $users = User::all();
        $users["Total_User"] = $users->count();
        return $users;
    }



    public function auth(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        // print_r($data);
        if (!$user) {
            return response([
                'message' => ['We dont any user with this email.']
            ], 404);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function importDemoData(Request $request)
    {    
        //Importing Slots
        $json = File::get(resource_path() .'/dataseed/slots.json');
        $data = json_decode($json, true);
        foreach ($data as $obj) {
      
            Slots::create(array(
                'id' => $obj['id'], 'block' => $obj['block'], 'block_seat_number' => $obj['block_seat_number'], 'is_near_to_lift' => $obj['is_near_to_lift'], 'is_reserved_seat' => $obj['is_reserved_seat'], 'seat_status' => $obj['seat_status'], 'booking_holds_upto' => $obj['booking_holds_upto'], 'created_at' => $obj['created_at'], 'updated_at' => $obj['updated_at']
            ));
        }

        //Importing Users

        $json = File::get(resource_path() .'/dataseed/users.json');
        $data = json_decode($json, true);
        foreach ($data as $obj) {
      
            User::create(array(
                'id' => $obj['id'], 'name' => $obj['name'], 'email' => $obj['email'], 'phoneno' => $obj['phoneno'], 'user_type' => $obj['user_type'], 'is_active_user' => $obj['is_active_user'], 'created_at' => $obj['created_at'], 'updated_at' => $obj['updated_at']
            ));
        }


        echo "Done";
    }
}
