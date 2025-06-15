<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'config.php';

// Get user information for better personalization
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username, role, instrument, section FROM users WHERE id = ?";
$user_stmt = $mysqli->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Practice Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Custom calendar styling */
        .fc-theme-standard .fc-scrollgrid {
            border: none;
        }
        
        .fc .fc-button-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .fc .fc-button-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .fc .fc-button-primary:disabled {
            background: #e5e7eb;
            color: #9ca3af;
        }
        
        .fc-event {
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .fc-day-today {
            background: rgba(99, 102, 241, 0.1) !important;
        }
        
        .fc-daygrid-day:hover {
            background: rgba(139, 92, 246, 0.05);
        }
        
        .event-approved { background: linear-gradient(135deg, #10b981, #059669); }
        .event-pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .event-rejected { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .event-my-session { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        
        .calendar-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
            backdrop-filter: blur(10px);
        }
        
        .legend-color {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>
<<body class="bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 min-h-screen font-sans text-gray-800">
    <div class="container mx-auto p-4 lg:p-6">
        <?php
        // Include components
        include 'components/header.php';
        
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <!-- Welcome Card -->
        <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/20 mb-6 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold gradient-text mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Practice Schedule Calendar
                    </h2>
                    <p class="text-gray-600">Manage your practice sessions • <?php echo htmlspecialchars($user_data['instrument'] ?? 'Musician'); ?> • <?php echo htmlspecialchars($user_data['section'] ?? 'Band Member'); ?></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button id="quick-add-btn" class="btn-music bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i>Quick Add
                    </button>
                    <button id="export-btn" class="btn-music bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Controls and Filters -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">            <!-- Filter Controls -->
            <div class="lg:col-span-1">
                <div class="filter-control p-4 rounded-2xl shadow-lg animate-slide-up">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-filter mr-2 text-purple-600"></i>Filters
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status-filter" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="all">All Sessions</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        
                        <!-- View Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">View</label>
                            <select id="view-filter" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="all">All Sessions</option>
                                <option value="my-sessions">My Sessions Only</option>
                            </select>
                        </div>
                        
                        <?php if ($user_data['role'] === 'admin'): ?>
                        <!-- Admin: Section Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                            <select id="section-filter" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="all">All Sections</option>
                                <option value="Brass">Brass</option>
                                <option value="Woodwind">Woodwind</option>
                                <option value="Percussion">Percussion</option>
                                <option value="Strings">Strings</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Refresh Button -->
                        <button id="refresh-calendar" class="w-full bg-gradient-to-r from-purple-500 to-pink-600 text-white px-4 py-2 rounded-lg font-medium hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="legend-enhanced p-4 rounded-2xl shadow-lg mt-4 animate-slide-up" style="animation-delay: 0.1s;">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>Legend
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="legend-item-enhanced p-2 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="legend-color event-approved w-4 h-4 rounded"></div>
                                <span class="text-sm font-medium">Approved</span>
                            </div>
                        </div>
                        <div class="legend-item-enhanced p-2 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="legend-color event-pending w-4 h-4 rounded"></div>
                                <span class="text-sm font-medium">Pending</span>
                            </div>
                        </div>
                        <div class="legend-item-enhanced p-2 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="legend-color event-rejected w-4 h-4 rounded"></div>
                                <span class="text-sm font-medium">Rejected</span>
                            </div>
                        </div>
                        <div class="legend-item-enhanced p-2 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="legend-color event-my-session w-4 h-4 rounded"></div>
                                <span class="text-sm font-medium">My Sessions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Container -->
            <div class="lg:col-span-3">
                <div class="calendar-enhanced p-6 rounded-2xl shadow-lg animate-slide-up" style="animation-delay: 0.2s;">
                    <div id="calendar" class="min-h-[600px]"></div>
                </div>
            </div>
        </div>
          <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="calendar-stats-card bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 rounded-xl shadow-lg animate-fade-in" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100">Approved</p>
                        <p class="text-2xl font-bold" id="approved-count">-</p>
                    </div>
                    <i class="fas fa-check-circle text-3xl text-green-200"></i>
                </div>
            </div>
            <div class="calendar-stats-card bg-gradient-to-r from-yellow-500 to-orange-600 text-white p-4 rounded-xl shadow-lg animate-fade-in" style="animation-delay: 0.4s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100">Pending</p>
                        <p class="text-2xl font-bold" id="pending-count">-</p>
                    </div>
                    <i class="fas fa-clock text-3xl text-yellow-200"></i>
                </div>
            </div>
            <div class="calendar-stats-card bg-gradient-to-r from-purple-500 to-pink-600 text-white p-4 rounded-xl shadow-lg animate-fade-in" style="animation-delay: 0.5s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100">My Sessions</p>
                        <p class="text-2xl font-bold" id="my-sessions-count">-</p>
                    </div>
                    <i class="fas fa-user text-3xl text-purple-200"></i>
                </div>
            </div>
            <div class="calendar-stats-card bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4 rounded-xl shadow-lg animate-fade-in" style="animation-delay: 0.6s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100">This Month</p>
                        <p class="text-2xl font-bold" id="month-total-count">-</p>
                    </div>
                    <i class="fas fa-calendar text-3xl text-blue-200"></i>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex justify-center space-x-4 animate-fade-in" style="animation-delay: 0.7s;">
            <a href="dashboard.php" class="bg-white/80 backdrop-blur-sm text-gray-700 hover:text-purple-600 px-6 py-3 rounded-xl font-medium transition-all duration-300 hover:shadow-lg border border-white/20">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
            <a href="request.php" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 hover:shadow-lg">
                <i class="fas fa-plus mr-2"></i>New Request
            </a>
        </div>
    </div>    <!-- Event Details Modal -->
    <div id="event-modal" class="fixed inset-0 modal-backdrop z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content-enhanced rounded-2xl shadow-2xl max-w-md w-full max-h-96 overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Session Details</h3>
                        <button id="close-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div id="modal-content" class="space-y-4">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Modal -->
    <div id="quick-add-modal" class="fixed inset-0 modal-backdrop z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content-enhanced rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Quick Add Session</h3>
                        <button id="close-quick-add" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="quick-add-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" id="quick-date" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" id="quick-start" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" id="quick-end" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Practice Goal</label>
                            <input type="text" id="quick-goal" class="form-input-enhanced w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., Scale practice, Solo rehearsal" required>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" id="cancel-quick-add" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all duration-300">Add Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    let calendar;
    let currentEvents = [];
    const currentUserId = <?php echo $user_id; ?>;
    const userRole = '<?php echo $user_data['role']; ?>';
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCalendar();
        initializeEventHandlers();
        loadEvents();
        updateStats();
    });

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                loadEvents(successCallback, failureCallback);
            },
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            dateClick: function(info) {
                showQuickAdd(info.date);
            },
            eventDidMount: function(info) {
                // Add custom styling based on event properties
                const event = info.event;
                const status = event.extendedProps.status;
                const isMySession = event.extendedProps.user_id == currentUserId;
                
                // Remove default styling
                info.el.style.background = '';
                info.el.style.borderColor = '';
                
                // Apply custom classes
                if (isMySession) {
                    info.el.classList.add('event-my-session');
                } else {
                    switch(status) {
                        case 'approved':
                            info.el.classList.add('event-approved');
                            break;
                        case 'pending':
                            info.el.classList.add('event-pending');
                            break;
                        case 'rejected':
                            info.el.classList.add('event-rejected');
                            break;
                    }
                }
                
                // Add hover effect
                info.el.style.cursor = 'pointer';
                info.el.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '1000';
                });
                info.el.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = 'auto';
                });
            }
        });
        
        calendar.render();
    }

    function loadEvents(successCallback = null, failureCallback = null) {
        const filters = {
            status: document.getElementById('status-filter').value,
            view: document.getElementById('view-filter').value,
            section: document.getElementById('section-filter')?.value || 'all'
        };
        
        const queryParams = new URLSearchParams(filters).toString();
        
        fetch(`fetchEvents.php?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                currentEvents = data;
                updateStats();
                if (successCallback) {
                    successCallback(data);
                } else {
                    calendar.removeAllEvents();
                    calendar.addEventSource(data);
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                if (failureCallback) {
                    failureCallback(error);
                }
            });
    }

    function updateStats() {
        const stats = {
            approved: 0,
            pending: 0,
            mysessions: 0,
            monthTotal: 0
        };
        
        const currentMonth = new Date().getMonth();
        const currentYear = new Date().getFullYear();
        
        currentEvents.forEach(event => {
            const eventDate = new Date(event.start);
            
            if (eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear) {
                stats.monthTotal++;
            }
            
            switch(event.extendedProps.status) {
                case 'approved':
                    stats.approved++;
                    break;
                case 'pending':
                    stats.pending++;
                    break;
            }
            
            if (event.extendedProps.user_id == currentUserId) {
                stats.mySession++;
            }
        });
        
        document.getElementById('approved-count').textContent = stats.approved;
        document.getElementById('pending-count').textContent = stats.pending;
        document.getElementById('my-sessions-count').textContent = stats.mySession;
        document.getElementById('month-total-count').textContent = stats.monthTotal;
    }

    function initializeEventHandlers() {
        // Filter handlers
        document.getElementById('status-filter').addEventListener('change', loadEvents);
        document.getElementById('view-filter').addEventListener('change', loadEvents);
        if (document.getElementById('section-filter')) {
            document.getElementById('section-filter').addEventListener('change', loadEvents);
        }
        document.getElementById('refresh-calendar').addEventListener('click', loadEvents);
        
        // Modal handlers
        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('event-modal').classList.add('hidden');
        });
        
        document.getElementById('event-modal').addEventListener('click', (e) => {
            if (e.target.id === 'event-modal') {
                document.getElementById('event-modal').classList.add('hidden');
            }
        });
        
        // Quick add handlers
        document.getElementById('quick-add-btn').addEventListener('click', () => showQuickAdd());
        document.getElementById('close-quick-add').addEventListener('click', closeQuickAdd);
        document.getElementById('cancel-quick-add').addEventListener('click', closeQuickAdd);
        document.getElementById('quick-add-modal').addEventListener('click', (e) => {
            if (e.target.id === 'quick-add-modal') {
                closeQuickAdd();
            }
        });
        
        document.getElementById('quick-add-form').addEventListener('submit', handleQuickAdd);
        
        // Export handler
        document.getElementById('export-btn').addEventListener('click', exportCalendar);
    }

    function showEventDetails(event) {
        const modal = document.getElementById('event-modal');
        const content = document.getElementById('modal-content');
        
        const statusBadge = {
            'approved': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Approved</span>',
            'pending': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending</span>',
            'rejected': '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Rejected</span>'
        };
        
        const isMySession = event.extendedProps.user_id == currentUserId;
        
        content.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-800">${event.title}</h4>
                ${statusBadge[event.extendedProps.status] || ''}
            </div>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span class="text-gray-600">Date: ${event.start.toLocaleDateString()}</span>
                </div>
                
                <div class="flex items-center">
                    <i class="fas fa-clock w-5 text-gray-400"></i>
                    <span class="text-gray-600">Time: ${event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                </div>
                
                ${event.extendedProps.username ? `
                <div class="flex items-center">
                    <i class="fas fa-user w-5 text-gray-400"></i>
                    <span class="text-gray-600">Musician: ${event.extendedProps.username}</span>
                </div>
                ` : ''}
                
                ${event.extendedProps.instrument ? `
                <div class="flex items-center">
                    <i class="fas fa-music w-5 text-gray-400"></i>
                    <span class="text-gray-600">Instrument: ${event.extendedProps.instrument}</span>
                </div>
                ` : ''}
                
                ${isMySession ? `
                <div class="mt-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center">
                        <i class="fas fa-star text-purple-500 mr-2"></i>
                        <span class="text-purple-700 font-medium">Your Session</span>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        modal.classList.remove('hidden');
    }

    function showQuickAdd(selectedDate = null) {
        const modal = document.getElementById('quick-add-modal');
        const dateInput = document.getElementById('quick-date');
        
        if (selectedDate) {
            dateInput.value = selectedDate.toISOString().split('T')[0];
        } else {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
        
        modal.classList.remove('hidden');
    }

    function closeQuickAdd() {
        document.getElementById('quick-add-modal').classList.add('hidden');
        document.getElementById('quick-add-form').reset();
    }

    function handleQuickAdd(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('date', document.getElementById('quick-date').value);
        formData.append('start_time', document.getElementById('quick-start').value);
        formData.append('end_time', document.getElementById('quick-end').value);
        formData.append('target_goal', document.getElementById('quick-goal').value);
        formData.append('quick_add', '1');
        
        fetch('request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeQuickAdd();
                loadEvents();
                showNotification('Practice session added successfully!', 'success');
            } else {
                showNotification(data.message || 'Error adding session', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adding session', 'error');
        });
    }

    function exportCalendar() {
        const events = calendar.getEvents();
        const exportData = events.map(event => ({
            title: event.title,
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : event.start.toISOString(),
            status: event.extendedProps.status
        }));
        
        const dataStr = JSON.stringify(exportData, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `practice-calendar-${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        showNotification('Calendar exported successfully!', 'success');
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
        
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'info': 'bg-blue-500',
            'warning': 'bg-yellow-500'
        }[type] || 'bg-blue-500';
        
        notification.classList.add(bgColor);
        notification.innerHTML = `
            <div class="flex items-center text-white">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    </script>
</body>
</html>
