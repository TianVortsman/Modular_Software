<?php
namespace App\Controllers;

use App\Services\DatabaseService;

/**
 * Access Controller
 * Handles all access control and device management operations
 */
class AccessController
{
    private $db;

    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = DatabaseService::getMainDatabase();
    }

    /**
     * Get access activity for a specific device or all devices
     * 
     * @param string|null $deviceId Optional device ID to filter by
     * @param string|null $startDate Optional start date (YYYY-MM-DD)
     * @param string|null $endDate Optional end date (YYYY-MM-DD)
     * @param int $page Page number for pagination
     * @param int $perPage Items per page
     * @return array Access activity data or error
     */
    public function getAccessActivity($deviceId = null, $startDate = null, $endDate = null, $page = 1, $perPage = 50)
    {
        try {
            $this->db->connect();
            
            // Build the base query
            $query = "
                SELECT 
                    ae.id,
                    ae.date_time,
                    ae.device_id,
                    d.device_name,
                    d.device_location,
                    ae.major_event_type,
                    ae.minor_event_type,
                    ae.raw_data,
                    ae.created_at
                FROM 
                    access.access_events ae
                LEFT JOIN 
                    access.devices d ON ae.device_id = d.device_id
                WHERE 
                    1=1
            ";
            
            $params = [];
            $paramIndex = 1;
            
            // Add device filter if provided
            if ($deviceId) {
                $query .= " AND ae.device_id = $" . $paramIndex;
                $params[] = $deviceId;
                $paramIndex++;
            }
            
            // Add date range filters if provided
            if ($startDate) {
                $query .= " AND ae.date_time >= $" . $paramIndex;
                $params[] = $startDate . ' 00:00:00';
                $paramIndex++;
            }
            
            if ($endDate) {
                $query .= " AND ae.date_time <= $" . $paramIndex;
                $params[] = $endDate . ' 23:59:59';
                $paramIndex++;
            }
            
            // Add ordering
            $query .= " ORDER BY ae.date_time DESC";
            
            // Add pagination
            $query .= " LIMIT $" . $paramIndex . " OFFSET $" . ($paramIndex + 1);
            $params[] = $perPage;
            $params[] = ($page - 1) * $perPage;
            
            // Execute the query
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                throw new \Exception("Failed to retrieve access activity: " . $this->db->getLastError());
            }
            
            // Get total count for pagination
            $countQuery = "
                SELECT COUNT(*) as total
                FROM access.access_events ae
                WHERE 1=1
            ";
            
            $countParams = [];
            $countParamIndex = 1;
            
            if ($deviceId) {
                $countQuery .= " AND ae.device_id = $" . $countParamIndex;
                $countParams[] = $deviceId;
                $countParamIndex++;
            }
            
            if ($startDate) {
                $countQuery .= " AND ae.date_time >= $" . $countParamIndex;
                $countParams[] = $startDate . ' 00:00:00';
                $countParamIndex++;
            }
            
            if ($endDate) {
                $countQuery .= " AND ae.date_time <= $" . $countParamIndex;
                $countParams[] = $endDate . ' 23:59:59';
                $countParamIndex++;
            }
            
            $countResult = $this->db->query($countQuery, $countParams);
            if (!$countResult) {
                throw new \Exception("Failed to get total count: " . $this->db->getLastError());
            }
            
            $totalRow = $this->db->fetchRow($countResult);
            $totalCount = (int)$totalRow['total'];
            
            // Fetch all rows
            $events = [];
            while ($row = $this->db->fetchRow($result)) {
                // Parse the raw_data JSON if it exists
                if (isset($row['raw_data'])) {
                    $row['raw_data'] = json_decode($row['raw_data'], true);
                }
                $events[] = $row;
            }
            
            return [
                'success' => true,
                'events' => $events,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_items' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage)
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error in AccessController->getAccessActivity: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
} 