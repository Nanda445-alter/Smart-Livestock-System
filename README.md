# 🐄 Smart Livestock Management & Supplement Recommendation System

## 📋 Project Overview

This is a **production-ready academic PBL project** that integrates three subjects:

1. **Web Technologies (MAIN)** - HTML5, CSS3, JavaScript, PHP, MySQL
2. **Design & Analysis of Algorithms (DAA)** - Greedy, Binary Search, Dynamic Programming, Graph/BFS
3. **Operating Systems (OS)** - Process Management, CPU Scheduling, File/Memory Management, Deadlock

### 🎯 Project Goals

Build a full-stack web application where farmers can:
- Register and manage user accounts securely
- Add and track livestock details (age, weight, milk yield, health status)
- Get AI-powered supplement recommendations
- Upload and manage farm documents/reports
- View analytics dashboards with milk production trends
- Search animals efficiently using advanced algorithms

---

## 🗂️ Project Structure

```
Smart-Livestock-System/
│
├── frontend/                          # Web UI (HTML5, CSS3, JavaScript)
│   ├── index.html                    # Landing page
│   ├── dashboard.html                # Main dashboard
│   ├── livestock.html                # Animal management
│   ├── supplements.html              # Supplement recommendations
│   ├── uploads.html                  # File management
│   ├── css/
│   │   └── style.css                # Responsive styling
│   └── js/
│       ├── main.js                  # Core functionality
│       ├── validation.js            # Form validation
│       └── chart.js                 # Chart visualization
│
├── backend/                           # Backend Logic (PHP)
│   ├── config.php                   # Database configuration
│   ├── auth.php                     # Authentication module
│   ├── livestock.php                # Animal CRUD operations
│   ├── supplements.php              # Supplement database
│   ├── logout.php                   # Logout handler
│   └── api/                         # REST API Endpoints
│       ├── login.php               # User login
│       ├── register.php            # User registration
│       ├── get_animals.php         # Fetch all animals
│       ├── search_animal.php       # Binary search API
│       ├── recommend_supplements.php  # Greedy algorithm API
│       ├── get_supplements.php     # Get all supplements
│       ├── get_dashboard_stats.php # Dashboard data
│       └── [more API endpoints]
│
├── algorithms/                        # DAA Algorithm Implementations
│   ├── greedy_supplement.php        # Greedy Algorithm (Supplement optimization)
│   ├── binary_search.php            # Binary Search (Animal lookup)
│   ├── dynamic_programming.php      # DP (Milk production prediction)
│   └── graph_bfs.php                # Graph/BFS (Farm network analysis)
│
├── os_simulation/                     # OS Concept Simulations
│   ├── process_management.php       # Process creation & state management
│   ├── cpu_scheduling.php           # Round Robin & FCFS scheduling
│   ├── file_management.php          # File system operations
│   ├── memory_management.php        # Virtual memory allocation
│   └── deadlock_simulation.php      # Deadlock detection & prevention
│
├── database/
│   ├── schema.sql                   # Database schema & tables
│   └── sample_data.sql              # Sample data for testing
│
├── uploads/                           # User file storage directory
│
└── README.md, DOCUMENTATION.md, VIVA_QUESTIONS.md
```

---

## 🗄️ Database Design

### Tables Structure

1. **users** - User accounts and authentication
2. **animals** - Livestock details
3. **supplements** - Supplement database
4. **milk_production** - Daily milk production tracking
5. **recommendations** - Saved supplement recommendations
6. **uploads** - File management
7. **process_log** - OS process simulation
8. **resource_locks** - Deadlock simulation
9. **farms_network** - Farm graph for disease spread
10. **farm_connections** - Farm connections (graph edges)

---

## 🌐 PART 1: WEB TECHNOLOGIES

### Frontend Features

#### 1. **Semantic HTML5**
- Proper document structure
- Accessible form elements
- Video/audio support ready

#### 2. **Responsive CSS3**
- Mobile-first design
- Flexbox & Grid layouts
- CSS animations & transitions
- Dark mode ready

#### 3. **Interactive JavaScript**
- Real-time form validation
- AJAX API calls
- DOM manipulation
- Event handling

### Backend Features

#### 1. **User Authentication**
```php
- Bcrypt password hashing
- Session management
- Login/Logout functionality
- Account registration with validation
```

#### 2. **RESTful API Endpoints**
```
GET    /api/get_animals.php          - Fetch all animals
POST   /api/create_animal.php        - Add new animal
PUT    /api/update_animal.php        - Update animal
DELETE /api/delete_animal.php        - Delete animal
GET    /api/search_animal.php        - Search animals (Binary Search)
POST   /api/recommend_supplements.php - Get recommendations (Greedy)
GET    /api/get_supplements.php      - All supplements
GET    /api/get_dashboard_stats.php  - Dashboard statistics
```

#### 3. **MVC-like Architecture**
- Separated concerns (config, auth, models)
- Reusable functions
- Modular design

---

## 🧠 PART 2: DAA IMPLEMENTATION

### 1. **Greedy Algorithm** ⭐

**Location:** `algorithms/greedy_supplement.php`

**Problem:** Maximize nutrition within budget constraint

**Algorithm:**
```
Step 1: Get all supplements for animal type
Step 2: Calculate nutrition-to-cost ratio for each supplement
Step 3: Sort by ratio (descending) - GREEDY CHOICE
Step 4: Iteratively select supplements until budget exhausted
Step 5: Return optimized recommendations
```

**Time Complexity:** O(n log n) - due to sorting
**Space Complexity:** O(n)

**Example:**
- Budget: ₹500
- Supplements sorted by nutrition/cost:
  1. Soybean Meal (45% protein, ₹12/kg) → Ratio: 3.75
  2. Sesame Cake (42% protein, ₹14/kg) → Ratio: 3.0
  3. Corn Grain (9% protein, ₹8.50/kg) → Ratio: 1.06
- Recommendation: Buy Soybean Meal first, then Sesame Cake, then Corn Grain

---

### 2. **Binary Search** ⭐

**Location:** `algorithms/binary_search.php`

**Problem:** Efficiently search animals by ID

**Algorithm:**
```
Step 1: Fetch all animals for user (sorted by ID)
Step 2: Initialize left = 0, right = n-1
Step 3: While left <= right:
        mid = (left + right) / 2
        if animals[mid].id == target → FOUND
        if animals[mid].id < target → search right half
        else → search left half
Step 4: Return found animal or null
```

**Time Complexity:** O(log n)
**Space Complexity:** O(1)

**Example:**
- Array: [1, 5, 8, 12, 15, 20, 25, 30]
- Search for 15:
  - mid = 12 < 15 → search right
  - mid = 25 > 15 → search left
  - mid = 20 > 15 → search left
  - mid = 15 = 15 → FOUND!

---

### 3. **Dynamic Programming** ⭐

**Location:** `algorithms/dynamic_programming.php`

**Problem:** Predict milk production and optimize feeding schedule

**Algorithm:**
```
Step 1: Get historical milk production (last 30 days)
Step 2: Build DP table: dp[i] = optimal production up to day i
Step 3: For each day:
        dp[i] = max(dp[i-1] + current, dp[i-1])
Step 4: Calculate trend using linear regression
Step 5: Generate feeding recommendations based on trend
```

**Time Complexity:** O(n * m) - n days, m supplements
**Space Complexity:** O(n * m)

**Example:**
- Historical data: [20L, 22L, 21L, 23L, 25L, ...]
- DP computes optimal feeding for maximum production
- Predicts next day: 26L (trend increasing)
- Recommendation: Increase high-protein supplements

---

### 4. **Graph & BFS** ⭐

**Location:** `algorithms/graph_bfs.php`

**Problem:** Simulate disease spread through farm network

**Algorithm:**
```
Step 1: Build adjacency list from farm connections
Step 2: Start BFS from infected farm
Step 3: Queue = [start_farm]
Step 4: While queue not empty:
        current = queue.pop()
        for each adjacent farm:
            if not visited → mark visited, add to queue
Step 5: Return infection spread pattern
```

**Time Complexity:** O(V + E) - V farms, E connections
**Space Complexity:** O(V + E)

**Example:**
- Farms: A, B, C, D, E
- Connections: A-B, B-C, C-D, D-E
- Disease starts at A:
  - Day 1: A infected
  - Day 2: B infected
  - Day 3: C infected
  - Day 4: D infected
  - Day 5: E infected

---

## ⚙️ PART 3: OPERATING SYSTEM CONCEPTS

### 1. **Process Management**

**Location:** `os_simulation/process_management.php`

**Concept:** Each user request is treated as a process with lifecycle

**Process States:**
```
READY     → Process waiting for CPU
RUNNING   → Process is executing
WAITING   → Process waiting for I/O
BLOCKED   → Process blocked on resource
COMPLETED → Process finished
```

**Implementation:**
```php
// Create process for each operation
$process = createProcess($userId, 'animal_add');
// State transitions
updateProcessState($processId, 'running');
updateProcessState($processId, 'completed');
```

---

### 2. **CPU Scheduling**

**Location:** `os_simulation/cpu_scheduling.php`

**Algorithms Implemented:**

#### **Round Robin**
```
Time Quantum: 100ms
Each process gets 100ms CPU time
If not finished, goes to end of queue
```

**Example:**
```
Process P1: 50ms    → Completes in 100ms quantum
Process P2: 200ms   → 100ms (quantum) + 100ms (next quantum)
Process P3: 150ms   → 100ms + 50ms
```

#### **FCFS (First Come First Served)**
```
Process executed in arrival order
No preemption
```

---

### 3. **File Management**

**Location:** `os_simulation/file_management.php`

**Operations:**
```php
createFile($userId, $fileData)      // Upload file
readFile($uploadId)                 // Read file info
deleteFile($uploadId)               // Soft delete
listUserFiles($userId)              // Directory listing
getFileSystemStats($userId)         // Storage usage
```

**Features:**
- File size validation (5MB max)
- File type filtering (PDF, JPG, PNG, DOC, DOCX)
- Unique filename generation
- Storage statistics

---

### 4. **Memory Management**

**Location:** `os_simulation/memory_management.php`

**Concept:** Simulate memory allocation using sessions

**Operations:**
```php
allocateMemory($userId, $key, $data)        // Allocate memory block
deallocateMemory($key)                      // Free memory
getMemoryStatistics()                       // Memory usage stats
checkMemoryFragmentation()                  // Fragmentation analysis
```

**Example:**
```
Session memory allocated for animal data: 2KB
Session memory allocated for supplements: 5KB
Total: 7KB used
Fragmentation: 15%
```

---

### 5. **Deadlock Simulation**

**Location:** `os_simulation/deadlock_simulation.php`

**Deadlock Conditions:**
```
1. Mutual Exclusion   → Resource can be held by only one process
2. Hold and Wait      → Process holds resource while waiting for another
3. No Preemption      → Resource cannot be forcibly taken
4. Circular Wait      → Circular chain of processes waiting for resources
```

**Prevention Strategy:**
```php
requestResource($processId, $resourceName)  // Request resource
releaseResource($processId, $resourceName)  // Release resource
detectDeadlock($processId, $resourceName)   // Check for deadlock
isSafeToAllocate()                          // Banker's Algorithm check
deadlockRecovery($processId)                // Recovery mechanism
```

---

## 🚀 INSTALLATION & SETUP

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- Composer (optional)

### Step-by-Step Setup

#### 1. **Clone Project**
```bash
git clone [repo-url]
cd Smart-Livestock-System
```

#### 2. **Database Setup**
```bash
mysql -u root -p < database/schema.sql
```

#### 3. **Update Database Config**
Edit `backend/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'livestock_db');
```

#### 4. **Set Permissions**
```bash
chmod -R 755 uploads/
chmod -R 755 os_simulation/
```

#### 5. **Start Server**
```bash
php -S localhost:8000
```

#### 6. **Access Application**
```
http://localhost:8000/frontend/index.html
http://localhost:8000/demo.html (Algorithm Demos)
```

---

## 📊 Testing the Project

### 🔐 Sample Data for Presentations

**Test Accounts:**
- **Username:** `farmer_john`, `farmer_sarah`, `farmer_mike`
- **Password:** `password123` (for all accounts)

**Algorithm Demo Data:**
- **Binary Search:** Animals with IDs 1-10 (perfect for step-by-step demo)
- **Greedy Algorithm:** Supplements with ratios 2.0, 1.5, 1.0, 0.8
- **Dynamic Programming:** 30-day milk trends (increasing/stable/decreasing)
- **Graph/BFS:** 4-farm network with clear connections
- **CPU Scheduling:** 6 processes with burst times 4, 6, 8, 10 (RR quantum=2)

### Register New Account
```
Username: farmer1
Email: farmer1@example.com
Password: TestPass123!
Farm Name: My Farm
```

### Add Animals
```
Name: Bessie
Type: Cow
Age: 5 years
Weight: 600 kg
Daily Milk: 25 liters
```

### Get Recommendations
```
Select Animal: Bessie
Budget: ₹500
Algorithm: Greedy (maximizes nutrition per rupee)
```

### Search Animals (Binary Search)
```
Enter ID: 1 → Fast binary search lookup
Enter Name: Bes → Linear search for pattern matching
```

---

## 📘 DOCUMENTATION SECTIONS

### For Web Technologies Faculty

**Key Explanations:**
1. **Frontend Architecture**
   - HTML5 semantic structure
   - CSS3 responsive design
   - JavaScript vanilla (no frameworks)
   - AJAX for real-time data

2. **Backend Implementation**
   - PHP OOP concepts
   - SQL query optimization
   - Session management
   - Error handling

3. **Database Design**
   - Normalization principles
   - Foreign key relationships
   - Indexes for performance

### For DAA Faculty

**Algorithm Explanations:**
1. **Greedy Algorithm**
   - Proof of correctness
   - Time/space complexity analysis
   - Real-world application

2. **Binary Search**
   - Prerequisite: sorted data
   - Comparison with linear search
   - Edge cases handling

3. **Dynamic Programming**
   - Memoization technique
   - Recurrence relation
   - Optimal substructure

4. **Graph & BFS**
   - Graph representation
   - Queue-based traversal
   - Connected components

### For OS Faculty

**Concept Demonstrations:**
1. **Process Management**
   - State transition diagram
   - Process lifecycle
   - Context switching

2. **CPU Scheduling**
   - Scheduling algorithm comparison
   - Gantt chart visualization
   - Average waiting time calculation

3. **Synchronization**
   - Deadlock detection
   - Resource allocation graph
   - Recovery mechanisms

---

## 🎓 VIVA QUESTIONS & ANSWERS

[See VIVA_QUESTIONS.md for comprehensive Q&A]

---

## 📈 Future Enhancements

1. Advanced Analytics
   - Machine learning for milk prediction
   - Anomaly detection
   - Herd management AI

2. Mobile App
   - React Native / Flutter app
   - Offline functionality
   - Push notifications

3. IoT Integration
   - Sensor data from wearables
   - Real-time health monitoring
   - Automated feeding systems

4. Blockchain
   - Supply chain tracking
   - Immutable records
   - Smart contracts

---

## 📝 License

This project is created for academic purposes.

---

## 👨‍💼 Author & Credits

**Academic PBL Project**
- Integrating: Web Tech, Algorithms, Operating Systems
- Production-ready implementation
- Fully documented code

---

**Happy Coding! 🚀**
# Smart-Livestock-System
