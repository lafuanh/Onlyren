use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PaymentController;

Route::apiResource('rooms', RoomController::class);
Route::apiResource('reservations', ReservationController::class);
Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
Route::get('/calendar/room/{id}', [CalendarController::class, 'showRoomCalendar']);
