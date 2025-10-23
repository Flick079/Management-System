<?php
require_once __DIR__ . '/../../configs/database.php';

// Build the base query
// Build the base query
$query = "SELECT 
            b.booking_id,
            b.customer_name,
            b.age,
            b.gender,
            b.contact_number,
            b.check_in_date,
            b.check_out_date,
            b.booking_status,
            b.total_cost,
            b.down_payment,
            b.booking_status,
            r.room_id,
            GROUP_CONCAT(DISTINCT r.room_number ORDER BY r.room_number SEPARATOR ', ') AS room_numbers,
            GROUP_CONCAT(DISTINCT rt.name ORDER BY r.room_number SEPARATOR ', ') AS room_types
          FROM bookings b
          JOIN booking_rooms br ON b.booking_id = br.booking_id
          JOIN rooms r ON br.room_id = r.room_id
          JOIN room_types rt ON r.room_type_id = rt.room_type_id";

// Initialize where conditions array
$whereConditions = [];
$params = [];

// 1. Handle search term filter
if (!empty($_GET['search_term'])) {
    $whereConditions[] = "(b.booking_id LIKE :search_term OR b.customer_name LIKE :search_term OR b.contact_number LIKE :search_term)";
    $params[':search_term'] = '%' . $_GET['search_term'] . '%';
}

// 2. Handle status filter (ADD THIS NEW SECTION)
$status = $_GET['status'] ?? '';
if (!empty($status)) {
    $whereConditions[] = "b.booking_status = :status";
    $params[':status'] = $status;
}

// 3. Handle date range filters (your existing date filters)
if (!empty($_GET['from_date'])) {
    $whereConditions[] = "b.check_in_date >= :from_date";
    $params[':from_date'] = $_GET['from_date'];
}

if (!empty($_GET['to_date'])) {
    $whereConditions[] = "b.check_out_date <= :to_date";
    $params[':to_date'] = $_GET['to_date'];
}

// Add where conditions if any exist
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Continue with the rest of your existing query (GROUP BY, ORDER BY etc.)
$query .= " GROUP BY b.booking_id
            ORDER BY b.check_in_date DESC, b.booking_id DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($bookings) > 0) {
    foreach ($bookings as $booking) {
        // Format dates
        $check_in = date('M d, Y', strtotime($booking['check_in_date']));
        $check_out = date('M d, Y', strtotime($booking['check_out_date']));
        
        // Format total cost
        $down_payment = '₱' . number_format($booking['total_cost']/2, 2);
        $total_cost = '₱' . number_format($booking['total_cost'], 2);

        
        // Status badge color
        $status_class = '';
        switch ($booking['booking_status']) {
            case 'Confirmed':
                $status_class = 'bg-success';
                break;
            case 'Ongoing':
                $status_class = 'bg-primary';
                break;
            case 'Pending':
                $status_class = 'bg-warning text-dark';
                break;
            case 'Cancelled':
                $status_class = 'bg-danger';
                break;
            case 'Completed':
                $status_class = 'bg-info';
                break;
            case 'Expired':
                $status_class = 'bg-danger';
                break;
            case 'No show':
                $status_class = 'bg-warning text-dark';
                break;
            default:
                $status_class = 'bg-secondary';
        }
        

        // Escape output for security
        $escaped_booking_id = htmlspecialchars($booking['booking_id'], ENT_QUOTES);
        $escaped_customer_name = htmlspecialchars($booking['customer_name'], ENT_QUOTES);
        $escaped_contact_number = htmlspecialchars($booking['contact_number'], ENT_QUOTES);
        $escaped_age = htmlspecialchars($booking['age'], ENT_QUOTES);
        $escaped_gender = htmlspecialchars($booking['gender'], ENT_QUOTES);
        $escaped_check_in_date = htmlspecialchars($booking['check_in_date'], ENT_QUOTES);
        $escaped_check_out_date = htmlspecialchars($booking['check_out_date'], ENT_QUOTES);
        $escaped_down_payment = number_format($booking['total_cost'] / 2, 2);
        $escaped_booking_status = htmlspecialchars($booking['booking_status'], ENT_QUOTES);
        $today = date('Y-m-d');
        $checkoutDate = date('Y-m-d', strtotime($booking['check_out_date']));
        echo "
        <tr>
        <td>
            <div class='fw-bold'>{$escaped_customer_name}</div>
            <small class='text-muted'>{$escaped_contact_number}</small>
        </td>
        <td>
            <div class='d-flex flex-column gap-1'>
                " . implode('', array_map(function($room_num, $room_type) {
                    $escaped_room_num = htmlspecialchars(trim($room_num), ENT_QUOTES);
                    $escaped_room_type = htmlspecialchars(trim($room_type), ENT_QUOTES);
                    return "<div>
                                <span class='badge bg-light text-dark border me-2'>{$escaped_room_num}</span>
                                <span class='text-muted'>{$escaped_room_type}</span>
                            </div>";
                }, explode(', ', $booking['room_numbers']), explode(', ', $booking['room_types']))) . "
            </div>
        </td>
        <td>{$check_in}</td>
        <td>{$check_out}</td>
        <td>{$total_cost}</td>
        <td class='text-center'>{$down_payment}</td>
        <td><span class='badge {$status_class}'>{$escaped_booking_status}</span></td>
        <td>
            <!-- Button to trigger the actions modal -->
            <button 
                class='btn btn-sm btn-secondary' 
                data-bs-toggle='modal' 
                data-bs-target='#actionsModal{$escaped_booking_id}'
            >
                <i class='bi bi-gear'></i> Edit
            </button>";
            if ($checkoutDate == $today && $booking['booking_status'] == 'Ongoing') {
                echo '<button class="btn btn-success btn-sm" 
                      onclick="showAdditionalChargesModal('.$booking['booking_id'].', \''.$booking['total_cost'].'\', \''.$booking['down_payment'].'\')">
                      <i class="bi bi-check-circle"></i> Booking Done
                      </button>';
            }
            echo "

            <!-- Actions Modal for this booking -->
            <div class='modal fade' id='actionsModal{$escaped_booking_id}' data-booking-id='123' tabindex='-1' aria-labelledby='actionsModalLabel{$escaped_booking_id}' aria-hidden='true'>
                <div class='modal-dialog modal-lg'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h4 class='modal-title' id='actionsModalLabel{$escaped_booking_id}'>Booking #{$escaped_booking_id} - {$escaped_customer_name}</h4>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <form action='update_booking.php' method='POST'>
                                <input type='hidden' name='booking_id' value='{$escaped_booking_id}'>
                                
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Full Name</label>
                                            <input type='text' class='form-control' name='customer_name' value='{$escaped_customer_name}' required>
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Contact Number</label>
                                            <input type='text' class='form-control' name='contact_number' value='{$escaped_contact_number}' required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class='row'>
                                    <div class='col-md-3'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Age</label>
                                            <input type='number' class='form-control' name='age' value='{$escaped_age}' required>
                                        </div>
                                    </div>
                                    <div class='col-md-3'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Gender</label>
                                            <select class='form-control' name='gender' required>
                                                <option value='Male'" . ($booking['gender'] == 'Male' ? ' selected' : '') . ">Male</option>
                                                <option value='Female'" . ($booking['gender'] == 'Female' ? ' selected' : '') . ">Female</option>
                                                <option value='Other'" . ($booking['gender'] == 'Other' ? ' selected' : '') . ">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Booking Status</label>
                                            <select class='form-control' name='booking_status' required>
                                                <option value='Confirmed'" . ($booking['booking_status'] == 'Confirmed' ? ' selected' : '') . ">Confirmed</option>
                                                <option value='Ongoing'" . ($booking['booking_status'] == 'Ongoing' ? ' selected' : '') . ">Ongoing</option>
                                                <option value='Pending'" . ($booking['booking_status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                                                <option value='Cancelled'" . ($booking['booking_status'] == 'Cancelled' ? ' selected' : '') . ">Cancelled</option>
                                                <option value='Completed'" . ($booking['booking_status'] == 'Completed' ? ' selected' : '') . ">Completed</option>
                                                <option value='Expired'" . ($booking['booking_status'] == 'Expired' ? ' selected' : '') . ">Expired</option>
                                                <option value='No show'" . ($booking['booking_status'] == 'No show' ? ' selected' : '') . ">No show</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Check-in Date</label>
                                            <input type='date' class='form-control' name='check_in_date' value='{$escaped_check_in_date}' required>
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Check-out Date</label>
                                            <input type='date' class='form-control' name='check_out_date' value='{$escaped_check_out_date}' required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Total Cost</label>
                                            <input type='text' class='form-control' value='{$total_cost}' disabled>
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'>Down Payment</label>
                                            <input type='text' class='form-control' name='down_payment' value='{$down_payment}' step='0.01' required disabled>
                                        </div>
                                    </div>
                                </div>
                                
                            
<div class='mb-3'>
    <label class='form-label'>Assigned Rooms</label>
    <div class='border p-2 rounded'>";
        
        // Get room IDs from the database for this booking
        $room_ids_query = "SELECT br.room_id, r.room_number, rt.name AS room_type, br.additional_persons
        FROM booking_rooms br 
        JOIN rooms r ON br.room_id = r.room_id 
        JOIN room_types rt ON r.room_type_id = rt.room_type_id 
        WHERE br.booking_id = :booking_id";
        $room_stmt = $pdo->prepare($room_ids_query);
        $room_stmt->execute([':booking_id' => $booking['booking_id']]);
        $room_data = $room_stmt->fetchAll(PDO::FETCH_ASSOC);

        

      // Generate the room entries
foreach ($room_data as $room) {
    $escaped_room_id = htmlspecialchars($room['room_id'], ENT_QUOTES);
    $escaped_room_num = htmlspecialchars($room['room_number'], ENT_QUOTES);
    $escaped_room_type = htmlspecialchars($room['room_type'], ENT_QUOTES);
    $escaped_additional_persons = htmlspecialchars($room['additional_persons'] ?? 0, ENT_QUOTES);

    echo "<div class='d-flex justify-content-between align-items-center mb-2' data-room-id='{$escaped_room_id}'>
        <div>
            <span class='badge bg-light text-dark border me-2'>{$escaped_room_num}</span>
            <span class='text-muted'>{$escaped_room_type}</span>
            <input type='hidden' name='room_ids[]' value='{$escaped_room_id}'>
        </div>
        <div class='d-flex align-items-center'>
            <div class='me-2'>
                <label class='form-label small mb-0'>Additional Persons</label>
                <input type='number' class='form-control form-control-sm additional-persons' 
                    name='additional_persons[]' min='0' value='{$escaped_additional_persons}' style='width: 70px;'>
            </div>
            <button type='button' class='btn btn-sm btn-outline-danger'>
                <i class='bi bi-trash'></i> Remove
            </button>
        </div>
    </div>
    <div class='room-details' style='display: block;'>
        <div class='alert alert-info'>
            <div class='d-flex justify-content-between'>
                <div>
                    Room: <span class='room-number-display'>{$escaped_room_num}</span><br>
                    Type: <span class='room-type-display'>{$escaped_room_type}</span>
                </div>
                <div>
                    Additional Persons: <span class='additional-persons-display'>{$escaped_additional_persons}</span>
                </div>
            </div>
        </div>
    </div>";
}  echo "
        <button type='button' class='btn btn-sm btn-primary mt-2 add-room'>
            <i class='bi bi-plus'></i> Add Room
        </button>
    </div>
</div>
                                <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                    <button type='submit' class='btn btn-primary'>Save Changes</button>
                                    <button type='button' class='btn btn-danger' onclick='confirmDelete({$escaped_booking_id})'>
                                        <i class='bi bi-trash'></i> Delete Booking
                                    </button>
                                    
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        </tr>";
    }

    
} else {
    echo "<tr>
            <td colspan='8' class='text-center py-4'>No bookings found</td>
          </tr>";
}