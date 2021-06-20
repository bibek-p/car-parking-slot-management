
# Cart Parking Slot Booking Automations.

As per the problems and requirements I have made 8 Api's endpoint to solve the business problems with satisfying the requirements.

## Database And Tables.

### Table : Users
|Column  | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `name` | `varchar` |  Stores the user name |
| `email` | `varchar` |  Stores the user email |
| `phoneno` | `varchar` |  Stores the user phoneno |
| `user_type` | `int` |  Stores the type of user . if type is **"1"** then this user is general user.If **"2"** then this user is differently-abled and pregnant women|
| `is_active_user` | `int` |  Stores active user or blocked user |

**Note** : I did not add the login management so i did not use an ay password here. If we want to use login management then we can add a password column.



### Table : Slots
|Column  | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `block` | `varchar` |  It stores block name of slot. If parking has more blocks. |
| `block_seat_number` | `int` |  It stores the slot number of that perticular block. |
| `is_near_to_lift` | `int` |  It stores is slot near to lift.if type is **"1"** then near to lift .If **"0"** not near to lift. |
| `is_reserved_seat` | `int` |  Its stores is it is_reserved slot for  differently-abled and pregnant women. if type is **"1"** then YES .If **"0"** then NO.|
| `seat_status` | `int` | It stores the seat staus. ( **1** : On Hold , **0** : Free , **2** : Confirmed ) |
| `booking_holds_upto` | `varchar` |  It stores upto what time it occupied according to the seat_status |

**Note** : if seat_status is **1** or **2** then slot is occupied.


### Table : Bookings
|Column  | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `user_id` | `int` |  It stores  **Users.id** |
| `slot_id` | `int` |  It stores  **Slots.id** |
| `booking_time` | `timestamp` | Stores the current timestamp of bookings.|
| `is_user_car_parked` | `int` | Store the staus of car parked or not.|
 
**Note** : if is_user_car_parked is ( **0** : User not entered to the slot yet. **1** : User checked in into the slot and **2** : User checked out from slot.


## Environment Variables & Database

To run this project, you will need to add the following environment variables to your .env file and need to create DB with saame name mentioned in env

`DB_HOST=127.0.0.1`

`DB_PORT=3306`

`DB_DATABASE=slots_man`

`DB_USERNAME=sammy`

`DB_PASSWORD=password`


`LIMT_CAPING_PERCENTAGE_FOR_AVOID_WAITING_TIME=50`  (If 50% capacity is utilized, then 15 mins extra wait time will be eliminated (for both reserved and general).)

`HOLD_BOKING_TIME_IN_MIN=15` (15 mins extra wait time).


`BEFORE_BOKING_TIME_IN_MIN=15` (15 mins prior to arrival).
  


## Migrations and importing dummy data.

To run the migration and impoting dummy data in user table and slots table

**Step : 1**
```bash
  php artisan migrate
```
it will create all the required table in DB.

**Step : 2**
```bash
  php artisan serve
```
it will start the local server.

**Step :3**

Call this endpoint with base url in postman
```http
  GET api/importdemodata
```
it will import all required sample data in user and slots table




## API Reference

**Get the Postman Collection :** [Click To Get For Import in Postman](https://www.postman.com/collections/526cebc145a599caf4d0)

#### 1. Generating Auth Token For Access API's.

```http
  GET api/auth
```

|Body Parameter |  
| :-------- |  
| {"email":"bibekispythondeveloper@gmail.com"}|  


#### 2. To get all registered users.

```http
  GET api/getallusers
```

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json


#### 3. To book slot.

```http
  GET api/book
```

|Body Parameter |  
| :-------- |  
| {"user_id":2}| 

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json


#### 4. To Checkin to slot.

```http
  GET api/slotcheckin
```

|Body Parameter |  
| :-------- |  
| {"user_id":2,"bookingid":1}| 

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json
  


 #### 5. To Checkout From slot.

```http
  GET api/slotcheckout
```

|Body Parameter |  
| :-------- |  
| {"user_id":2,"bookingid":1}| 

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json 



 #### 6. To get all available slot.

```http
  GET api/getallavailableslot
```

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json 



 #### 7. To get all occupied slot.

```http
  GET api/getalloccupiedslot
```

**Header :**

Authorization:Bearer {AUTH_TOKEN}

Accept:application/json 



### *Note : For auth validation i am using sanctum.