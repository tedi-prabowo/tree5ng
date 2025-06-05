<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Function to safely output JSON
function safeJsonResponse($data) {
    $json = json_encode($data);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON encoding error: " . json_last_error_msg());
        echo json_encode(['success' => false, 'message' => 'JSON encoding error']);
    } else {
        echo $json;
    }
    exit;
}

// Function to get appropriate icon based on file type
function getFileIcon($type, $fileType = null) {
    if ($type === 'folder') {
        return 'fas fa-folder';
    }
    
    // File icons based on file type
    switch ($fileType) {
        case 'document':
            return 'fas fa-file-alt';
        case 'image':
            return 'fas fa-file-image';
        case 'code':
            return 'fas fa-file-code';
        case 'media':
            return 'fas fa-file-video';
        case 'archive':
            return 'fas fa-file-archive';
        default:
            return 'fas fa-file';
    }
}

// Log the incoming request for debugging
error_log("=== NODE_ACTIONS.PHP START ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("Current working directory: " . getcwd());

try {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        safeJsonResponse(['success' => false, 'message' => 'Only POST requests allowed']);
    }

    // Check if database.php exists
    if (!file_exists('database.php')) {
        error_log("database.php file not found");
        safeJsonResponse(['success' => false, 'message' => 'Database configuration file not found']);
    }

    require_once 'database.php';
    error_log("database.php included successfully");

    $action = $_POST['action'] ?? '';
    error_log("Action requested: '$action'");

    if (empty($action)) {
        error_log("No action specified");
        safeJsonResponse(['success' => false, 'message' => 'No action specified']);
    }

    // Create database instance with error handling
    try {
        $db = new Database();
        error_log("Database object created successfully");
    } catch (Exception $e) {
        error_log("Database creation failed: " . $e->getMessage());
        safeJsonResponse(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    }

    switch ($action) {
        case 'add_node':
            error_log("Processing add_node request");
            $parentId = $_POST['parent_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? 'folder';
            $fileType = trim($_POST['file_type'] ?? '');
            
            // Validate input
            if (empty($name)) {
                safeJsonResponse(['success' => false, 'message' => 'Node name is required']);
            }
            
            if (strlen($name) > 100) {
                safeJsonResponse(['success' => false, 'message' => 'Node name too long (max 100 characters)']);
            }
            
            // Handle file type validation
            if ($type === 'file') {
                $validFileTypes = ['default', 'document', 'image', 'code', 'media', 'archive'];
                if (!in_array($fileType, $validFileTypes)) {
                    $fileType = 'default';
                }
            } else {
                $fileType = null; // Folders don't have file types
            }
            
            // Get appropriate icon
            $icon = getFileIcon($type, $fileType);
            
            error_log("Adding node with type: '$type', file_type: '$fileType', icon: '$icon'");
            
            $nodeId = $db->addNode($parentId, $name, $type, $icon, $fileType);
            
            safeJsonResponse([
                'success' => $nodeId !== false,
                'id' => $nodeId,
                'icon' => $icon,
                'file_type' => $fileType,
                'message' => $nodeId !== false ? 'Node added successfully' : 'Failed to add node'
            ]);
            break;

        case 'rename_node':
            error_log("Processing rename_node request");
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            
            if (empty($id)) {
                safeJsonResponse(['success' => false, 'message' => 'Node ID is required']);
            }
            
            if (empty($name)) {
                safeJsonResponse(['success' => false, 'message' => 'Node name is required']);
            }
            
            $result = $db->updateNode($id, $name);
            safeJsonResponse([
                'success' => $result,
                'message' => $result ? 'Node renamed successfully' : 'Failed to rename node'
            ]);
            break;

        case 'delete_node':
            error_log("Processing delete_node request");
            $id = $_POST['id'] ?? null;
            
            if (empty($id)) {
                safeJsonResponse(['success' => false, 'message' => 'Node ID is required']);
            }
            
            if ($db->hasChildren($id)) {
                safeJsonResponse(['success' => false, 'message' => 'Cannot delete node with children']);
            }
            
            $result = $db->deleteNode($id);
            safeJsonResponse([
                'success' => $result,
                'message' => $result ? 'Node deleted successfully' : 'Failed to delete node'
            ]);
            break;

        case 'move_node':
            error_log("Processing move_node request");
            $id = $_POST['id'] ?? null;
            $newParentId = $_POST['parent_id'] ?? null;
            
            // Prevent moving root node
            if ($id === 'root' || $id === '#' || empty($id)) {
                safeJsonResponse(['success' => false, 'message' => 'Cannot move root node']);
            }
            
            // Log the move operation
            error_log("MOVE_NODE: Received ID: '$id', Parent ID: '$newParentId'");
            
            // Validate input
            if (empty($id)) {
                error_log("MOVE_NODE: No ID provided");
                safeJsonResponse(['success' => false, 'message' => 'Node ID is required']);
            }
            
            // Convert empty string to null for parent_id
            if ($newParentId === '') {
                $newParentId = null;
            }
            
            try {
                // Check if trying to move node to itself or its descendant
                if ($newParentId && $db->isDescendant($newParentId, $id)) {
                    error_log("MOVE_NODE: Cannot move to descendant");
                    safeJsonResponse(['success' => false, 'message' => 'Cannot move node to its own descendant']);
                }
                
                // Check if new parent exists (if not null/root)
                if ($newParentId && !$db->nodeExists($newParentId)) {
                    error_log("MOVE_NODE: Parent does not exist: '$newParentId'");
                    safeJsonResponse(['success' => false, 'message' => 'Target parent does not exist']);
                }
                
                error_log("MOVE_NODE: About to call moveNode()");
                $result = $db->moveNode($id, $newParentId);
                error_log("MOVE_NODE: moveNode() returned: " . ($result ? 'true' : 'false'));
                
                safeJsonResponse([
                    'success' => $result,
                    'message' => $result ? 'Node moved successfully' : 'Failed to move node'
                ]);
                
            } catch (Exception $e) {
                error_log("MOVE_NODE: Exception in move operation: " . $e->getMessage());
                safeJsonResponse(['success' => false, 'message' => 'Move failed: ' . $e->getMessage()]);
            }
            break;

        case 'load_tree':
            error_log("Processing load_tree request");
            
            try {
                $nodes = $db->getAllNodes();
                error_log("Retrieved " . count($nodes) . " nodes from database");
                
                // Validate that we got an array
                if (!is_array($nodes)) {
                    error_log("getAllNodes() did not return an array");
                    safeJsonResponse(['success' => false, 'message' => 'Invalid data format from database']);
                }
                
                // Log first few nodes for debugging
                if (count($nodes) > 0) {
                    error_log("First node: " . print_r($nodes[0], true));
                }
                
                // Return the nodes directly (the frontend will transform them)
                safeJsonResponse($nodes);
                
            } catch (Exception $e) {
                error_log("Error in load_tree: " . $e->getMessage());
                safeJsonResponse(['success' => false, 'message' => 'Failed to load tree data: ' . $e->getMessage()]);
            }
            break;
            
        default:
            error_log("Invalid action requested: '$action'");
            safeJsonResponse(['success' => false, 'message' => 'Invalid action: ' . $action]);
            break;
    }

} catch (Exception $e) {
    error_log("Uncaught Exception in node_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    safeJsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Fatal error in node_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    safeJsonResponse(['success' => false, 'message' => 'Fatal server error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    error_log("Throwable in node_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    safeJsonResponse(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
?>