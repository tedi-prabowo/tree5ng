<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tree Management</title>
 
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
    
    <!-- jsTree CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden; 
        }

        #header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex-shrink: 0; 
        }

        #header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        #container {
            display: flex;
            flex: 1;
            position: relative;
            overflow: hidden; 
        }

        #left-pane {
            background-color: #f4f4f4;
            width: 300px;
            padding: 15px;
            transition: all 0.3s ease;
            overflow-x: hidden;
            overflow-y: auto; 
            border-right: 1px solid #ddd;
        }

        #left-pane.collapsed {
            width: 0;
            padding: 15px 0;
            overflow: hidden;
        }

        #right-pane {
            background-color: #e7e7e7;
            flex-grow: 1;
            padding: 1px;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        #content-display {
            display: none;
            flex-grow: 1;
            padding: 20px;
            background: white;
            margin: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #content-display.show {
            display: flex;
            flex-direction: column;
        }

        #collapse-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 6px;
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        #collapse-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Scrollbar styling */
        #left-pane::-webkit-scrollbar,
        #right-pane::-webkit-scrollbar {
            width: 8px;
        }

        #left-pane::-webkit-scrollbar-track,
        #right-pane::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #left-pane::-webkit-scrollbar-thumb,
        #right-pane::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        #left-pane::-webkit-scrollbar-thumb:hover,
        #right-pane::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* For Firefox */
        #left-pane,
        #right-pane {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        .modal-overlay {
            z-index: 9999;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        #menu-tree {
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .jstree {
            font-size: 12px;
        }
        
        .jstree-anchor .jstree-icon.fas.fa-folder.root-icon {
            color: #8B4513 !important; 
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .jstree-anchor .jstree-icon.fas.fa-folder:not(.root-icon) {
            color: #4A90E2 !important; 
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .jstree-anchor .jstree-icon.fas.fa-file {
            color: #50C878 !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-file-alt {
            color: #FF6B6B !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-file-image {
            color: #9B59B6 !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-file-code {
            color: #F39C12 !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-file-video {
            color: #E74C3C !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-file-archive {
            color: #34495E !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-external-link-alt.fixed-link-icon {
            color: #800080 !important; 
        }

        .jstree-anchor .jstree-icon.fas.fa-external-link-alt:not(.fixed-link-icon) {
            color: #FF4500 !important; 
        }

        .jstree-anchor .jstree-icon[class*="fa-"] {
            font-family: "Font Awesome 5 Free" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            font-size: 12px !important;
            line-height: 1 !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            position: relative;
            cursor: help;
        }

        .jstree-anchor .jstree-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            vertical-align: middle !important;
        }
        
        .jstree-anchor .jstree-anchor-text {
            display: inline-flex !important;
            align-items: center !important;
            vertical-align: middle !important;
        }
        
        .jstree-anchor:hover .jstree-icon {
            transform: scale(1.05) !important;
            transition: transform 0.2s ease !important;
        }

        .jstree-clicked .jstree-icon {
            animation: pulse 0.6s ease-in-out !important;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: normal;
            white-space: nowrap;
            z-index: 10000; 
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .tooltip.show {
            opacity: 1;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.9) transparent transparent transparent;
        }

        .tooltip-bottom::after {
            top: -10px;
            border-color: transparent transparent rgba(0, 0, 0, 0.9) transparent;
        }
        
        .icon-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .icon-option {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border: 2px solid transparent;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: white;
        }

        .icon-option:hover {
            border-color: #4A90E2;
            background-color: #f0f8ff;
        }

        .icon-option.selected {
            border-color: #4A90E2;
            background-color: #e3f2fd;
        }

        .icon-option i {
            margin-right: 8px;
            font-size: 16px;
        }

        .icon-option span {
            font-size: 12px;
            font-weight: 500;
        }

        /* Iframe styling */
        #content-display iframe {
            width: 100%;
            height: 100%; 
            border: none;
            display: block;
            border-radius: 4px;
        }

        /* URL validation styling */
        .url-input.invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1) !important;
        }

        .url-input.valid {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1) !important;
        }

        .url-validation-message {
            font-size: 12px;
            margin-top: 4px;
            transition: all 0.2s ease;
        }

        .url-validation-message.error {
            color: #ef4444;
        }

        .url-validation-message.success {
            color: #10b981;
        }

        .content-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .content-header i {
            font-size: 24px;
            margin-right: 12px;
        }

        .content-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .content-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }
    </style>  
</head>
<body class="bg-gray-100">
    <div id="header">
        <h1>My Custom Tree Manager</h1>
        <button id="collapse-btn" onclick="toggleLeftPane()">
            ☰ Toggle Menu
        </button>
    </div>

    <div id="container">
        <div id="left-pane">
            <!-- Tree Control Buttons -->
            <div class="mb-3 flex flex-wrap gap-2">
                <button 
                    id="expandAllBtn" 
                    class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded focus:outline-none focus:shadow-outline transition-colors"
                    title="Expand All Nodes"
                >
                    <i class="fas fa-expand-arrows-alt mr-1"></i>
                    Expand All
                </button>
                <button 
                    id="collapseAllBtn" 
                    class="bg-orange-500 hover:bg-orange-600 text-white text-xs px-3 py-1 rounded focus:outline-none focus:shadow-outline transition-colors"
                    title="Collapse All Nodes"
                >
                    <i class="fas fa-compress-arrows-alt mr-1"></i>
                    Collapse All
                </button>
            </div>
            
            <!-- Tree Container -->
            <div id="menu-tree" class="bg-white p-4 rounded shadow min-h-[400px]"></div>
        </div>

        <div id="right-pane">
            <div id="content-display">
                <div class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-mouse-pointer text-4xl mb-4"></i>
                        <p>Select a menu item to view details</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Node Modal -->
    <div id="addNodeModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-md mx-4">
            <h2 class="text-xl font-bold mb-4">Add New Node</h2>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nodeName">
                    Node Name
                </label>
                <input 
                    type="text" 
                    id="nodeName" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter node name"
                    maxlength="100"
                >
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nodeType">Node Type</label>
                <select 
                    id="nodeType" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    onchange="showIconSelector()"
                >
                    <option value="folder">Folder</option>
                    <option value="file">File</option>
                </select>
            </div>
            
            <!-- Icon Selector for Files -->
            <div id="iconSelector" class="mb-4" style="display: none;">
                <label class="block text-gray-700 text-sm font-bold mb-2">File Type & Icon</label>
                <div class="icon-selector">
                    <div class="icon-option selected" data-file-type="default" data-icon="fas fa-file">
                        <i class="fas fa-file" style="color: #50C878;"></i>
                        <span>Default</span>
                    </div>
                    <div class="icon-option" data-file-type="document" data-icon="fas fa-file-alt">
                        <i class="fas fa-file-alt" style="color: #FF6B6B;"></i>
                        <span>Document</span>
                    </div>
                    <div class="icon-option" data-file-type="image" data-icon="fas fa-file-image">
                        <i class="fas fa-file-image" style="color: #9B59B6;"></i>
                        <span>Image</span>
                    </div>
                    <div class="icon-option" data-file-type="code" data-icon="fas fa-file-code">
                        <i class="fas fa-file-code" style="color: #F39C12;"></i>
                        <span>Code</span>
                    </div>
                    <div class="icon-option" data-file-type="media" data-icon="fas fa-file-video">
                        <i class="fas fa-file-video" style="color: #E74C3C;"></i>
                        <span>Media</span>
                    </div>
                    <div class="icon-option" data-file-type="archive" data-icon="fas fa-file-archive">
                        <i class="fas fa-file-archive" style="color: #34495E;"></i>
                        <span>Archive</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button 
                    id="cancelNodeBtn" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Cancel
                </button>
                <button 
                    id="confirmNodeBtn" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Add Node
                </button>
            </div>
        </div>
    </div>

    <!-- Add External URL Modal -->
    <div id="addUrlModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="bg-orange-100 p-2 rounded-full mr-3">
                    <i class="fas fa-external-link-alt text-orange-500 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Add External URL</h2>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="urlTitle">
                    Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="urlTitle" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter link title"
                    maxlength="100"
                >
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="urlAddress">
                    URL <span class="text-red-500">*</span>
                </label>
                <input 
                    type="url" 
                    id="urlAddress" 
                    class="url-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="https://example.com"
                >
                <div id="urlValidationMessage" class="url-validation-message"></div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Tips for adding URLs:</p>
                        <ul class="text-xs space-y-1">
                            <li>• Always include http:// or https://</li>
                            <li>• Use descriptive titles for easy identification</li>
                            <li>• URLs will open in the right panel</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button 
                    id="cancelUrlBtn" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                >
                    Cancel
                </button>
                <button 
                    id="confirmUrlBtn" 
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                    disabled
                >
                    <i class="fas fa-external-link-alt mr-1"></i>
                    Add URL
                </button>
            </div>
        </div>
    </div>

    <!-- Move Node Modal -->
    <div id="moveNodeModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 p-2 rounded-full mr-3">
                    <i class="fas fa-arrows-alt text-blue-500 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Move Node</h2>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-600 mb-2">Moving node:</p>
                <div class="bg-gray-50 p-3 rounded border mb-4">
                    <div class="flex items-center">
                        <i id="moveNodeIcon" class="fas fa-folder text-blue-500 mr-2"></i>
                        <span id="moveNodeName" class="font-medium text-gray-800"></span>
                    </div>
                </div>
                
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Select New Parent:
                </label>
                <select 
                    id="newParentSelect" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                    <option value="">Loading...</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Only folder nodes can be selected as parents.
                </p>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button 
                    id="cancelMoveBtn" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                >
                    Cancel
                </button>
                <button 
                    id="confirmMoveBtn" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                >
                    <i class="fas fa-arrows-alt mr-1"></i>
                    Move Node
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 p-2 rounded-full mr-3">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Confirm Deletion</h2>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-600 mb-2">Are you sure you want to delete this node?</p>
                <div class="bg-gray-50 p-3 rounded border">
                    <div class="flex items-center">
                        <i id="deleteNodeIcon" class="fas fa-folder text-blue-500 mr-2"></i>
                        <span id="deleteNodeName" class="font-medium text-gray-800"></span>
                    </div>
                </div>
                <p class="text-sm text-red-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button 
                    id="cancelDeleteBtn" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                >
                    Cancel
                </button>
                <button 
                    id="confirmDeleteBtn" 
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors"
                >
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let treeInstance = null;
        let tooltip = null;
        let currentNodeId = null;
        let nodeIdCounter = 1;

        // Sample tree data
        const sampleTreeData = [
            {
                id: 'root',
                text: 'Root Directory',
                icon: 'fas fa-folder root-icon',
                state: { opened: true },
                data: { nodeType: 'root' },
                children: [
                    {
                        id: 'documents',
                        text: 'Documents',
                        icon: 'fas fa-folder',
                        data: { nodeType: 'folder' },
                        children: [
                            {
                                id: 'doc1',
                                text: 'Report.pdf',
                                icon: 'fas fa-file-alt',
                                data: { nodeType: 'file', fileType: 'document' }
                            },
                            {
                                id: 'doc2',
                                text: 'Presentation.pptx',
                                icon: 'fas fa-file-alt',
                                data: { nodeType: 'file', fileType: 'document' }
                            }
                        ]
                    },
                    {
                        id: 'images',
                        text: 'Images',
                        icon: 'fas fa-folder',
                        data: { nodeType: 'folder' },
                        children: [
                            {
                                id: 'img1',
                                text: 'photo1.jpg',
                                icon: 'fas fa-file-image',
                                data: { nodeType: 'file', fileType: 'image' }
                            },
                            {
                                id: 'img2',
                                text: 'logo.png',
                                icon: 'fas fa-file-image',
                                data: { nodeType: 'file', fileType: 'image' }
                            }
                        ]
                    },
                    {
                        id: 'projects',
                        text: 'Projects',
                        icon: 'fas fa-folder',
                        data: { nodeType: 'folder' },
                        children: [
                            {
                                id: 'proj1',
                                text: 'Website Code',
                                icon: 'fas fa-folder',
                                data: { nodeType: 'folder' },
                                children: [
                                    {
                                        id: 'code1',
                                        text: 'index.html',
                                        icon: 'fas fa-file-code',
                                        data: { nodeType: 'file', fileType: 'code' }
                                    },
                                    {
                                        id: 'code2',
                                        text: 'style.css',
                                        icon: 'fas fa-file-code',
                                        data: { nodeType: 'file', fileType: 'code' }
                                    }
                                ]
                            },
                            {
                                id: 'proj2',
                                text: 'Mobile App',
                                icon: 'fas fa-folder',
                                data: { nodeType: 'folder' }
                            }
                        ]
                    },
                    {
                        id: 'media',
                        text: 'Media Files',
                        icon: 'fas fa-folder',
                        data: { nodeType: 'folder' },
                        children: [
                            {
                                id: 'video1',
                                text: 'demo.mp4',
                                icon: 'fas fa-file-video',
                                data: { nodeType: 'file', fileType: 'media' }
                            }
                        ]
                    },
                    {
                        id: 'external_links',
                        text: 'External Links',
                        icon: 'fas fa-folder',
                        data: { nodeType: 'folder' },
                        children: [
                            {
                                id: 'link1',
                                text: 'Google',
                                icon: 'fas fa-external-link-alt',
                                data: { 
                                    nodeType: 'external_url', 
                                    url: 'https://www.google.com' 
                                }
                            },
                            {
                                id: 'link2',
                                text: 'GitHub',
                                icon: 'fas fa-external-link-alt',
                                data: { 
                                    nodeType: 'external_url', 
                                    url: 'https://www.github.com' 
                                }
                            }
                        ]
                    }
                ]
            }
        ];

        // Initialize the application
        $(document).ready(function() {
            initializeTree();
            initializeTooltips();
            initializeEventListeners();
            updateNodeCounter();
        });

        // Initialize jsTree
        function initializeTree() {
            $('#menu-tree').jstree({
                core: {
                    data: sampleTreeData,
                    check_callback: true,
                    themes: {
                        responsive: false
                    }
                },
                contextmenu: {
                    select_node: false,
                    items: function(node) {
                        return getContextMenuItems(node);
                    }
                },
                plugins: ['contextmenu', 'dnd', 'state', 'types']
            }).on('ready.jstree', function() {
                treeInstance = $('#menu-tree').jstree(true);
                console.log('Tree initialized successfully');
            }).on('select_node.jstree', function(e, data) {
                handleNodeSelection(data.node);
            }).on('move_node.jstree', function(e, data) {
                console.log('Node moved:', data);
            });
        }

        // Update node counter for unique IDs
        function updateNodeCounter() {
            const allNodes = treeInstance ? treeInstance.get_json('#', {flat: true}) : [];
            let maxId = 0;
            allNodes.forEach(node => {
                const match = node.id.match(/(\d+)$/);
                if (match) {
                    maxId = Math.max(maxId, parseInt(match[1]));
                }
            });
            nodeIdCounter = maxId + 1;
        }

        // Handle node selection
        function handleNodeSelection(node) {
            currentNodeId = node.id;
            displayNodeContent(node);
        }

        // Display content for selected node
// Display content for selected node
function displayNodeContent(node) {
    const contentDisplay = document.getElementById('content-display');
    const nodeType = node.data && node.data.nodeType ? node.data.nodeType : 'folder';
    
    let content = '';
    
    if (nodeType === 'external_url' && node.data && node.data.url) {
        // Display external URL in iframe
        content = `
            <div class="content-header">
                <i class="fas fa-external-link-alt text-orange-500"></i>
                <h2>${node.text}</h2>
            </div>
            <div class="content-info">
                <p><strong>Type:</strong> External Link</p>
                <p><strong>URL:</strong> <a href="${node.data.url}" target="_blank" class="text-blue-600 hover:underline">${node.data.url}</a></p>
            </div>
            <iframe src="${node.data.url}" style="flex-grow: 1; min-height: 400px;"></iframe>
        `;
    } else {
        // Display node information
        const fileType = node.data && node.data.fileType ? node.data.fileType : null;
        const iconClass = getNodeIcon(nodeType, fileType, node.id === 'root');
        const typeLabel = getNodeTypeLabel(nodeType, fileType);
        
        // Safely get parent text
        let parentText = 'None (Root)';
        if (node.parent && node.parent !== '#' && treeInstance) {
            try {
                const parentNode = treeInstance.get_node(node.parent);
                if (parentNode) {
                    parentText = parentNode.text;
                }
            } catch (e) {
                parentText = 'Unknown';
            }
        }
        
        content = `
            <div class="content-header">
                <i class="${iconClass}"></i>
                <h2>${node.text}</h2>
            </div>
            <div class="content-info">
                <p><strong>Type:</strong> ${typeLabel}</p>
                <p><strong>ID:</strong> ${node.id}</p>
                <p><strong>Parent:</strong> ${parentText}</p>
                <p><strong>Children:</strong> ${node.children ? node.children.length : 0}</p>
            </div>
            <div class="action-buttons">
                ${getActionButtons(node)}
            </div>
        `;
    }
    
    contentDisplay.innerHTML = content;
    contentDisplay.classList.add('show');
}

        // Get appropriate icon for node
        function getNodeIcon(nodeType, fileType, isRoot = false) {
            if (nodeType === 'external_url') return 'fas fa-external-link-alt text-orange-500';
            if (nodeType === 'root' || isRoot) return 'fas fa-folder text-yellow-700';
            if (nodeType === 'folder') return 'fas fa-folder text-blue-500';
            
            const fileIcons = {
                'default': 'fas fa-file text-green-500',
                'document': 'fas fa-file-alt text-red-400',
                'image': 'fas fa-file-image text-purple-500',
                'code': 'fas fa-file-code text-yellow-500',
                'media': 'fas fa-file-video text-red-500',
                'archive': 'fas fa-file-archive text-gray-600'
            };
            
            return fileIcons[fileType] || fileIcons['default'];
        }

        // Get node type label
        function getNodeTypeLabel(nodeType, fileType) {
            if (nodeType === 'external_url') return 'External Link';
            if (nodeType === 'root') return 'Root Directory';
            if (nodeType === 'folder') return 'Folder';
            
            const fileLabels = {
                'default': 'Default File',
                'document': 'Document File',
                'image': 'Image File',
                'code': 'Code File',
                'media': 'Media File',
                'archive': 'Archive File'
            };
            
            return fileLabels[fileType] || 'File';
        }

        // Get action buttons for node
        function getActionButtons(node) {
            const isRoot = node.id === 'root';
            const isFolder = !node.data || node.data.nodeType === 'folder' || node.data.nodeType === 'root';
            
            let buttons = '';
            
            if (isFolder) {
                buttons += `
                    <button class="btn btn-primary" onclick="showAddNodeModal('${node.id}')">
                        <i class="fas fa-plus"></i> Add Child
                    </button>
                    <button class="btn btn-warning" onclick="showAddUrlModal('${node.id}')">
                        <i class="fas fa-external-link-alt"></i> Add URL
                    </button>
                `;
            }
            
            if (!isRoot) {
                buttons += `
                    <button class="btn btn-success" onclick="showMoveNodeModal('${node.id}')">
                        <i class="fas fa-arrows-alt"></i> Move
                    </button>
                    <button class="btn btn-danger" onclick="showDeleteConfirmModal('${node.id}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                `;
            }
            
            return buttons;
        }

        // Context menu items
        function getContextMenuItems(node) {
            const isRoot = node.id === 'root';
            const isFolder = !node.data || node.data.nodeType === 'folder' || node.data.nodeType === 'root';
            
            let items = {};
            
            if (isFolder) {
                items.add_node = {
                    label: "Add Child Node",
                    icon: "fas fa-plus",
                    action: function() { showAddNodeModal(node.id); }
                };
                items.add_url = {
                    label: "Add External URL",
                    icon: "fas fa-external-link-alt",
                    action: function() { showAddUrlModal(node.id); }
                };
            }
            
            if (!isRoot) {
                items.move = {
                    label: "Move Node",
                    icon: "fas fa-arrows-alt",
                    action: function() { showMoveNodeModal(node.id); }
                };
                items.delete = {
                    label: "Delete Node",
                    icon: "fas fa-trash",
                    action: function() { showDeleteConfirmModal(node.id); }
                };
            }
            
            return items;
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Expand/Collapse buttons
            $('#expandAllBtn').click(function() {
                if (treeInstance) {
                    treeInstance.open_all();
                }
            });
            
            $('#collapseAllBtn').click(function() {
                if (treeInstance) {
                    treeInstance.close_all();
                }
            });

            // Modal event listeners
            initializeModalListeners();
        }

        // Initialize modal event listeners
        function initializeModalListeners() {
            // Add Node Modal
            $('#cancelNodeBtn').click(() => hideModal('addNodeModal'));
            $('#confirmNodeBtn').click(handleAddNode);
            $('#nodeName').keypress(function(e) {
                if (e.which === 13) handleAddNode();
            });

            // Add URL Modal
            $('#cancelUrlBtn').click(() => hideModal('addUrlModal'));
            $('#confirmUrlBtn').click(handleAddUrl);
            $('#urlAddress').on('input', validateUrl);
            $('#urlTitle').keypress(function(e) {
                if (e.which === 13 && !$('#confirmUrlBtn').prop('disabled')) handleAddUrl();
            });

            // Move Node Modal
            $('#cancelMoveBtn').click(() => hideModal('moveNodeModal'));
            $('#confirmMoveBtn').click(handleMoveNode);

            // Delete Confirmation Modal
            $('#cancelDeleteBtn').click(() => hideModal('deleteConfirmModal'));
            $('#confirmDeleteBtn').click(handleDeleteNode);

            // Icon selector
            $(document).on('click', '.icon-option', function() {
                $('.icon-option').removeClass('selected');
                $(this).addClass('selected');
            });
        }

        // Show/Hide modals
        function showModal(modalId) {
            $(`#${modalId}`).removeClass('hidden');
        }

        function hideModal(modalId) {
            $(`#${modalId}`).addClass('hidden');
            resetModalForms();
        }

        // Reset modal forms
        function resetModalForms() {
            $('#nodeName').val('');
            $('#nodeType').val('folder');
            $('#iconSelector').hide();
            $('.icon-option').removeClass('selected');
            $('.icon-option[data-file-type="default"]').addClass('selected');
            
            $('#urlTitle').val('');
            $('#urlAddress').val('').removeClass('valid invalid');
            $('#urlValidationMessage').text('');
            $('#confirmUrlBtn').prop('disabled', true);
        }

        // Show icon selector
        function showIconSelector() {
            const nodeType = $('#nodeType').val();
            if (nodeType === 'file') {
                $('#iconSelector').show();
            } else {
                $('#iconSelector').hide();
            }
        }

        // Validate URL
        function validateUrl() {
            const url = $('#urlAddress').val();
            const urlInput = $('#urlAddress');
            const message = $('#urlValidationMessage');
            const confirmBtn = $('#confirmUrlBtn');
            
            if (!url) {
                urlInput.removeClass('valid invalid');
                message.text('');
                confirmBtn.prop('disabled', true);
                return;
            }
            
            try {
                new URL(url);
                urlInput.removeClass('invalid').addClass('valid');
                message.text('Valid URL').removeClass('error').addClass('success');
                confirmBtn.prop('disabled', !$('#urlTitle').val().trim());
            } catch {
                urlInput.removeClass('valid').addClass('invalid');
                message.text('Please enter a valid URL (include http:// or https://)').removeClass('success').addClass('error');
                confirmBtn.prop('disabled', true);
            }
        }

        // Modal handlers
        function showAddNodeModal(parentId) {
            currentNodeId = parentId;
            showModal('addNodeModal');
            $('#nodeName').focus();
        }

        function showAddUrlModal(parentId) {
            currentNodeId = parentId;
            showModal('addUrlModal');
            $('#urlTitle').focus();
        }

        function showMoveNodeModal(nodeId) {
            const node = treeInstance.get_node(nodeId);
            if (!node) return;
            
            currentNodeId = nodeId;
            
            // Update modal content
            $('#moveNodeName').text(node.text);
            $('#moveNodeIcon').attr('class', node.icon);
            
            // Populate parent select
            populateParentSelect(nodeId);
            
            showModal('moveNodeModal');
        }

        function showDeleteConfirmModal(nodeId) {
            const node = treeInstance.get_node(nodeId);
            if (!node) return;
            
            currentNodeId = nodeId;
            
            // Update modal content
            $('#deleteNodeName').text(node.text);
            $('#deleteNodeIcon').attr('class', node.icon);
            
            showModal('deleteConfirmModal');
        }

        // Handle add node
        function handleAddNode() {
            const name = $('#nodeName').val().trim();
            const type = $('#nodeType').val();
            
            if (!name) {
                alert('Please enter a node name');
                return;
            }
            
            let nodeData = {
                id: `node_${nodeIdCounter++}`,
                text: name,
                data: { nodeType: type }
            };
            
            if (type === 'folder') {
                nodeData.icon = 'fas fa-folder';
            } else {
                const selectedOption = $('.icon-option.selected');
                const fileType = selectedOption.data('file-type');
                const icon = selectedOption.data('icon');
                
                nodeData.icon = icon;
                nodeData.data.fileType = fileType;
            }
            
            // Add node to tree
            treeInstance.create_node(currentNodeId, nodeData);
            
            hideModal('addNodeModal');
            updateNodeCounter();
        }

        // Handle add URL
        function handleAddUrl() {
            const title = $('#urlTitle').val().trim();
            const url = $('#urlAddress').val().trim();
            
            if (!title || !url) {
                alert('Please fill in both title and URL');
                return;
            }
            
            const nodeData = {
                id: `url_${nodeIdCounter++}`,
                text: title,
                icon: 'fas fa-external-link-alt',
                data: { 
                    nodeType: 'external_url',
                    url: url
                }
            };
            
            // Add URL node to tree
            treeInstance.create_node(currentNodeId, nodeData);
            
            hideModal('addUrlModal');
            updateNodeCounter();
        }

        // Handle move node
        function handleMoveNode() {
            const newParentId = $('#newParentSelect').val();
            
            if (!newParentId) {
                alert('Please select a new parent');
                return;
            }
            
            if (newParentId === currentNodeId) {
                alert('Cannot move node to itself');
                return;
            }
            
            // Check if trying to move to a descendant
            if (isDescendant(currentNodeId, newParentId)) {
                alert('Cannot move node to its own descendant');
                return;
            }
            
            // Move the node
            treeInstance.move_node(currentNodeId, newParentId, 'last');
            
            hideModal('moveNodeModal');
        }

        // Handle delete node
        function handleDeleteNode() {
            if (!currentNodeId) return;
            
            // Delete the node
            treeInstance.delete_node(currentNodeId);
            
            hideModal('deleteConfirmModal');
            
            // Clear content display if deleted node was selected
            const contentDisplay = document.getElementById('content-display');
            contentDisplay.innerHTML = `
                <div class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-mouse-pointer text-4xl mb-4"></i>
                        <p>Select a menu item to view details</p>
                    </div>
                </div>
            `;
            contentDisplay.classList.remove('show');
        }

        // Populate parent select dropdown
        function populateParentSelect(excludeNodeId) {
            const select = $('#newParentSelect');
            select.empty();
            
            // Get all folder nodes except the one being moved and its descendants
            const allNodes = treeInstance.get_json('#', {flat: true});
            const validParents = allNodes.filter(node => {
                const isFolder = !node.data || node.data.nodeType === 'folder' || node.data.nodeType === 'root';
                const isNotSelf = node.id !== excludeNodeId;
                const isNotDescendant = !isDescendant(excludeNodeId, node.id);
                
                return isFolder && isNotSelf && isNotDescendant;
            });
            
            validParents.forEach(node => {
                const depth = getNodeDepth(node.id);
                const indent = '  '.repeat(depth);
                const option = $(`<option value="${node.id}">${indent}${node.text}</option>`);
                select.append(option);
            });
            
            if (validParents.length === 0) {
                select.append('<option value="">No valid parents available</option>');
            }
        }

        // Check if nodeB is a descendant of nodeA
        function isDescendant(nodeA, nodeB) {
            const nodeAObj = treeInstance.get_node(nodeA);
            const allDescendants = treeInstance.get_json(nodeA, {flat: true});
            
            return allDescendants.some(desc => desc.id === nodeB);
        }

        // Get node depth in tree
        function getNodeDepth(nodeId) {
            let depth = 0;
            let currentNode = treeInstance.get_node(nodeId);
            
            while (currentNode && currentNode.parent !== '#') {
                depth++;
                currentNode = treeInstance.get_node(currentNode.parent);
            }
            
            return depth;
        }

        // Toggle left pane
        function toggleLeftPane() {
            const leftPane = document.getElementById('left-pane');
            const collapseBtn = document.getElementById('collapse-btn');
            
            if (!leftPane || !collapseBtn) return;
            
            leftPane.classList.toggle('collapsed');
            
            if (leftPane.classList.contains('collapsed')) {
                collapseBtn.innerHTML = '☰ Show Menu';
            } else {
                collapseBtn.innerHTML = '☰ Toggle Menu';
            }
        }

        // Tooltip functionality
        function initializeTooltips() {
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                document.body.appendChild(tooltip);
            }
            
            $('#menu-tree').off('mouseenter.tooltip').on('mouseenter.tooltip', '.jstree-icon[class*="fa-"]', function(e) {
                showTooltip(e, this);
            });
            
            $('#menu-tree').off('mouseleave.tooltip').on('mouseleave.tooltip', '.jstree-icon[class*="fa-"]', function() {
                hideTooltip();
            });
            
            $('#left-pane').off('scroll.tooltip').on('scroll.tooltip', hideTooltip);
        }

        function showTooltip(event, iconElement) {
            const $icon = $(iconElement);
            const $anchor = $icon.closest('.jstree-anchor');
            const nodeId = $anchor.parent().attr('id');
            const node = treeInstance ? treeInstance.get_node(nodeId) : null;
            
            if (!node) return;
            
            let tooltipText = getTooltipText(node);
            if (!tooltipText) return;
            
            tooltip.textContent = tooltipText;
            tooltip.className = 'tooltip show';
            positionTooltip(event, iconElement);
        }

        function hideTooltip() {
            if (tooltip) {
                tooltip.className = 'tooltip';
            }
        }

        function getTooltipText(node) {
            const nodeType = node.data && node.data.nodeType ? node.data.nodeType : 'folder';
            const fileType = node.data && node.data.fileType ? node.data.fileType : null;
            
            if (nodeType === 'external_url') {
                return `External Link: ${node.data.url}`;
            }
            if (nodeType === 'root') return 'Root Folder - Main directory';
            if (nodeType === 'folder') return 'Folder - Container for files and subfolders';
            if (nodeType === 'file') {
                const fileTypeLabels = {
                    'default': 'Default File - Generic file type',
                    'document': 'Document File - Text documents, PDFs, etc.',
                    'image': 'Image File - Pictures, graphics, photos',
                    'code': 'Code File - Programming source code',
                    'media': 'Media File - Videos, audio files',
                    'archive': 'Archive File - Compressed files, ZIP, RAR'
                };
                return fileTypeLabels[fileType] || 'File - Unknown type';
            }
            return null;
        }

        function positionTooltip(event, iconElement) {
            const iconRect = iconElement.getBoundingClientRect();
            
            if (!tooltip || tooltip.className !== 'tooltip show') return;
            
            const tooltipRect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            let left = iconRect.left + (iconRect.width / 2) - (tooltip.offsetWidth / 2);
            let top = iconRect.top - tooltip.offsetHeight - 8;
            
            if (left < 10) left = 10;
            if (left + tooltip.offsetWidth > viewportWidth - 10) {
                left = viewportWidth - tooltip.offsetWidth - 10;
            }
            
            if (top < 10) {
                top = iconRect.bottom + 8;
                tooltip.classList.add('tooltip-bottom');
            } else {
                tooltip.classList.remove('tooltip-bottom');
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        // Close modals when clicking outside
        $(document).click(function(e) {
            if ($(e.target).hasClass('modal-overlay')) {
                $(e.target).addClass('hidden');
                resetModalForms();
            }
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // ESC key to close modals
            if (e.keyCode === 27) {
                $('.modal-overlay:not(.hidden)').addClass('hidden');
                resetModalForms();
            }
        });
    </script>
</body>
</html>