<?php
class Database {
    private $db;
    private $dbFile = 'tree_menu.sqlite';

    public function __construct() {
        try {
            error_log("DATABASE: Initializing database connection");
            error_log("DATABASE: Current working directory: " . getcwd());
            error_log("DATABASE: Database file path: " . $this->dbFile);
            
            // Check if SQLite3 extension is loaded
            if (!extension_loaded('sqlite3')) {
                throw new Exception("SQLite3 extension is not loaded");
            }
            
            // Check directory permissions
            $dir = dirname($this->dbFile);
            if ($dir === '.') $dir = getcwd();
            
            if (!is_writable($dir)) {
                error_log("DATABASE: Directory is not writable: $dir");
                throw new Exception("Directory is not writable: $dir");
            }
            
            // Create/open SQLite database
            $this->db = new SQLite3($this->dbFile);
            
            if (!$this->db) {
                throw new Exception("Failed to create SQLite3 database");
            }
            
            error_log("DATABASE: SQLite3 database opened successfully");
            
            // Enable foreign key constraints
            $this->db->exec('PRAGMA foreign_keys = ON');
            
            // Set WAL mode for better concurrent access
            $this->db->exec('PRAGMA journal_mode = WAL');
            
            // Create/update nodes table
            $this->createOrUpdateTable();
            
            // Insert root node if it doesn't exist
            $this->ensureRootNode();
            
            error_log("DATABASE: Database initialization completed successfully");
            
        } catch(Exception $e) {
            error_log("DATABASE: Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function createOrUpdateTable() {
        try {
            // Check if table exists
            $tableExists = $this->db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='nodes'");
            
            if (!$tableExists) {
                // Create new table with all columns including file_type
                $createTableSQL = 'CREATE TABLE nodes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    parent_id INTEGER DEFAULT NULL,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT "folder",
                    icon TEXT NOT NULL DEFAULT "fas fa-folder",
                    position INTEGER DEFAULT 0,
                    file_type TEXT DEFAULT NULL,
                    FOREIGN KEY (parent_id) REFERENCES nodes(id) ON DELETE CASCADE
                )';
                
                $result = $this->db->exec($createTableSQL);
                if ($result === false) {
                    $error = $this->db->lastErrorMsg();
                    error_log("DATABASE: Failed to create table: $error");
                    throw new Exception("Failed to create nodes table: $error");
                }
                
                error_log("DATABASE: Nodes table created successfully with file_type column");
            } else {
                // Table exists, check if columns exist and add them if missing
                $this->addMissingColumns();
            }
            
        } catch (Exception $e) {
            error_log("DATABASE: Error creating/updating table: " . $e->getMessage());
            throw $e;
        }
    }

    private function addMissingColumns() {
        try {
            $result = $this->db->query("PRAGMA table_info(nodes)");
            $existingColumns = [];
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $existingColumns[] = $row['name'];
            }
            
            // Check and add position column
            if (!in_array('position', $existingColumns)) {
                error_log("DATABASE: Adding missing position column");
                $alterResult = $this->db->exec("ALTER TABLE nodes ADD COLUMN position INTEGER DEFAULT 0");
                
                if ($alterResult === false) {
                    $error = $this->db->lastErrorMsg();
                    error_log("DATABASE: Failed to add position column: $error");
                    throw new Exception("Failed to add position column: $error");
                }
                
                // Update existing rows to have proper position values
                $this->updateExistingPositions();
                error_log("DATABASE: Position column added successfully");
            }
            
            // Check and add file_type column
            if (!in_array('file_type', $existingColumns)) {
                error_log("DATABASE: Adding missing file_type column");
                $alterResult = $this->db->exec("ALTER TABLE nodes ADD COLUMN file_type TEXT DEFAULT NULL");
                
                if ($alterResult === false) {
                    $error = $this->db->lastErrorMsg();
                    error_log("DATABASE: Failed to add file_type column: $error");
                    throw new Exception("Failed to add file_type column: $error");
                }
                
                error_log("DATABASE: file_type column added successfully");
            }
            
            if (in_array('position', $existingColumns) && in_array('file_type', $existingColumns)) {
                error_log("DATABASE: All columns exist, table is up to date");
            }
            
        } catch (Exception $e) {
            error_log("DATABASE: Error adding missing columns: " . $e->getMessage());
            throw $e;
        }
    }

    private function updateExistingPositions() {
        try {
            // Get all nodes grouped by parent_id
            $result = $this->db->query("SELECT id, parent_id FROM nodes ORDER BY parent_id, id");
            $nodesByParent = [];
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $parentId = $row['parent_id'] ?: 'null';
                if (!isset($nodesByParent[$parentId])) {
                    $nodesByParent[$parentId] = [];
                }
                $nodesByParent[$parentId][] = $row['id'];
            }
            
            // Update position for each group
            foreach ($nodesByParent as $parentId => $nodeIds) {
                foreach ($nodeIds as $index => $nodeId) {
                    $stmt = $this->db->prepare("UPDATE nodes SET position = ? WHERE id = ?");
                    $stmt->bindValue(1, $index, SQLITE3_INTEGER);
                    $stmt->bindValue(2, $nodeId, SQLITE3_INTEGER);
                    $stmt->execute();
                }
            }
            
            error_log("DATABASE: Updated positions for existing nodes");
            
        } catch (Exception $e) {
            error_log("DATABASE: Error updating existing positions: " . $e->getMessage());
            // Don't throw here, as this is not critical for basic functionality
        }
    }

    private function ensureRootNode() {
        try {
            $checkRoot = $this->db->querySingle("SELECT COUNT(*) FROM nodes WHERE name = 'Root' AND parent_id IS NULL");
            
            if ($checkRoot == 0) {
                error_log("DATABASE: Creating root node");
                $stmt = $this->db->prepare('INSERT INTO nodes (parent_id, name, type, icon, position, file_type) VALUES (NULL, ?, ?, ?, ?, NULL)');
                if (!$stmt) {
                    throw new Exception("Failed to prepare root node statement: " . $this->db->lastErrorMsg());
                }
                
                $stmt->bindValue(1, 'Root', SQLITE3_TEXT);
                $stmt->bindValue(2, 'folder', SQLITE3_TEXT);
                $stmt->bindValue(3, 'fas fa-folder', SQLITE3_TEXT);
                $stmt->bindValue(4, 0, SQLITE3_INTEGER);
                
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception("Failed to create root node: " . $this->db->lastErrorMsg());
                }
                
                error_log("DATABASE: Root node created successfully");
            } else {
                error_log("DATABASE: Root node already exists");
            }
        } catch (Exception $e) {
            error_log("DATABASE: Error ensuring root node: " . $e->getMessage());
            throw $e;
        }
    }

    // Add a new node to the database
    public function addNode($parentId, $name, $type = 'folder', $icon = 'fas fa-folder', $fileType = null) {
        try {
            error_log("DATABASE: Adding node - Parent: $parentId, Name: $name, Type: $type, FileType: $fileType");
            
            // Validate parent_id if provided
            if ($parentId !== null && !$this->nodeExists($parentId)) {
                error_log("DATABASE: Parent node does not exist: $parentId");
                return false;
            }
            
            // Get the next position for this parent
            $position = $this->getNextPosition($parentId);
            
            $stmt = $this->db->prepare('INSERT INTO nodes (parent_id, name, type, icon, position, file_type) VALUES (?, ?, ?, ?, ?, ?)');
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare add node statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $parentId, SQLITE3_INTEGER);
            $stmt->bindValue(2, $name, SQLITE3_TEXT);
            $stmt->bindValue(3, $type, SQLITE3_TEXT);
            $stmt->bindValue(4, $icon, SQLITE3_TEXT);
            $stmt->bindValue(5, $position, SQLITE3_INTEGER);
            $stmt->bindValue(6, $fileType, SQLITE3_TEXT);
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("DATABASE: Failed to execute add node: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $nodeId = $this->db->lastInsertRowID();
            error_log("DATABASE: Node added successfully with ID: $nodeId");
            return $nodeId;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error adding node: " . $e->getMessage());
            return false;
        }
    }

    // Update node details
    public function updateNode($id, $name) {
        try {
            error_log("DATABASE: Updating node ID: $id, Name: $name");
            
            $stmt = $this->db->prepare('UPDATE nodes SET name = ? WHERE id = ?');
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare update statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $name, SQLITE3_TEXT);
            $stmt->bindValue(2, $id, SQLITE3_INTEGER);
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("DATABASE: Failed to execute update: " . $this->db->lastErrorMsg());
                return false;
            }
            
            error_log("DATABASE: Node updated successfully");
            return true;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error updating node: " . $e->getMessage());
            return false;
        }
    }

    // Delete a node
    public function deleteNode($id) {
        try {
            error_log("DATABASE: Deleting node ID: $id");
            
            // Check if node has children
            if ($this->hasChildren($id)) {
                error_log("DATABASE: Cannot delete node with children: $id");
                return false;
            }
            
            $stmt = $this->db->prepare('DELETE FROM nodes WHERE id = ?');
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare delete statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $id, SQLITE3_INTEGER);
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("DATABASE: Failed to execute delete: " . $this->db->lastErrorMsg());
                return false;
            }
            
            error_log("DATABASE: Node deleted successfully");
            return true;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error deleting node: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Move a node to a new parent
     */
    public function moveNode($nodeId, $newParentId) {
        try {
            error_log("DATABASE: MOVE_NODE: Starting move operation for ID: '$nodeId' to parent: '$newParentId'");
            
            // Validate the node exists
            if (!$this->nodeExists($nodeId)) {
                error_log("DATABASE: MOVE_NODE: Source node does not exist: $nodeId");
                return false;
            }
            
            // Validate new parent exists (if not null)
            if ($newParentId !== null && !$this->nodeExists($newParentId)) {
                error_log("DATABASE: MOVE_NODE: Target parent does not exist: $newParentId");
                return false;
            }
            
            // Check for circular reference
            if ($newParentId !== null && $this->isDescendant($newParentId, $nodeId)) {
                error_log("DATABASE: MOVE_NODE: Circular reference detected");
                return false;
            }
            
            // Get the next position for the new parent
            $position = $this->getNextPosition($newParentId);
            
            $stmt = $this->db->prepare('UPDATE nodes SET parent_id = ?, position = ? WHERE id = ?');
            if (!$stmt) {
                error_log("DATABASE: MOVE_NODE: Failed to prepare move statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $newParentId, SQLITE3_INTEGER);
            $stmt->bindValue(2, $position, SQLITE3_INTEGER);
            $stmt->bindValue(3, $nodeId, SQLITE3_INTEGER);
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("DATABASE: MOVE_NODE: Failed to execute move: " . $this->db->lastErrorMsg());
                return false;
            }
            
            error_log("DATABASE: MOVE_NODE: Update query executed successfully");
            return true;
            
        } catch (Exception $e) {
            error_log("DATABASE: MOVE_NODE: Database error: " . $e->getMessage());
            return false;
        }
    }

    // Get all nodes to rebuild the tree
    public function getAllNodes() {
        try {
            error_log("DATABASE: Getting all nodes");
            
            $result = $this->db->query('SELECT * FROM nodes ORDER BY parent_id, position, id');
            if (!$result) {
                error_log("DATABASE: Failed to query nodes: " . $this->db->lastErrorMsg());
                return [];
            }
            
            $nodes = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $nodes[] = $row;
            }
            
            error_log("DATABASE: Retrieved " . count($nodes) . " nodes");
            return $nodes;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error getting all nodes: " . $e->getMessage());
            return [];
        }
    }

    public function hasChildren($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM nodes WHERE parent_id = ?");
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare hasChildren statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("DATABASE: Failed to execute hasChildren: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $row = $result->fetchArray(SQLITE3_NUM);
            return $row[0] > 0;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error checking children: " . $e->getMessage());
            return false;
        }
    }

    public function nodeExists($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM nodes WHERE id = ?");
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare nodeExists statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("DATABASE: Failed to execute nodeExists: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $row = $result->fetchArray(SQLITE3_NUM);
            return $row[0] > 0;
            
        } catch (Exception $e) {
            error_log("DATABASE: Error checking node existence: " . $e->getMessage());
            return false;
        }
    }

    public function isDescendant($nodeId, $ancestorId) {
        try {
            if ($nodeId === null || $ancestorId === null) {
                return false;
            }
            
            if ($nodeId == $ancestorId) {
                return true;
            }
            
            $stmt = $this->db->prepare("SELECT parent_id FROM nodes WHERE id = ?");
            if (!$stmt) {
                error_log("DATABASE: Failed to prepare isDescendant statement: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $stmt->bindValue(1, $nodeId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("DATABASE: Failed to execute isDescendant: " . $this->db->lastErrorMsg());
                return false;
            }
            
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if (!$row || $row['parent_id'] === null) {
                return false;
            }
            
            return $this->isDescendant($row['parent_id'], $ancestorId);
            
        } catch (Exception $e) {
            error_log("DATABASE: Error checking descendant: " . $e->getMessage());
            return false;
        }
    }

    private function getNextPosition($parentId) {
        try {
            if ($parentId === null) {
                $result = $this->db->query("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM nodes WHERE parent_id IS NULL");
            } else {
                $stmt = $this->db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM nodes WHERE parent_id = ?");
                if (!$stmt) {
                    error_log("DATABASE: Failed to prepare getNextPosition statement: " . $this->db->lastErrorMsg());
                    return 1;
                }
                
                $stmt->bindValue(1, $parentId, SQLITE3_INTEGER);
                $result = $stmt->execute();
            }
            
            if (!$result) {
                error_log("DATABASE: Failed to execute getNextPosition: " . $this->db->lastErrorMsg());
                return 1;
            }
            
            $row = $result->fetchArray(SQLITE3_ASSOC);
            return $row['next_pos'];
            
        } catch (Exception $e) {
            error_log("DATABASE: Error getting next position: " . $e->getMessage());
            return 1;
        }
    }

    public function getConnection() {
        return $this->db;
    }
    
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>