1| <div align="center">
2|   <h1>🎮 SOF2Panel</h1>
3|   <p><b>A modern, high-performance Game Server Management Panel built specifically for Soldier of Fortune 2.</b></p>
4| </div>
5| 
6| <br>
7| 
8| SOF2Panel is a premium, web-based management dashboard designed to seamlessly provision, control, and manage Soldier of Fortune 2 game servers across multiple Linux nodes. Built with Laravel 11, it provides a modern server control panel for admins, community managers, and game hosts.
9| 
10| ## ✨ Key Features
11| 
12| - **Multi-Node Architecture**: Connect multiple Linux VPS instances via SSH. The panel acts as a master controller and provisions servers across your nodes instantly.
13| - **Automated Provisioning**: With a single click, SOF2Panel creates a dedicated Linux user, assigns ports, safely copies base game files, and spins up a dedicated `screen` session.
14| - **Granular Permissions**: Give your community managers access to specific servers. Control exactly what they can do (e.g., Allow "Start/Stop", but deny "Web FTP" and "RCON").
15| - **Live Player Monitoring**: View real-time player counts and server status directly from the dashboard.
16| - **Web FTP File Manager**: Browse, upload, download, and manage your `.cfg` and `.pk3` files directly from the browser without needing FileZilla.
17| - **Web RCON Console**: Instantly send RCON commands (kick, map change, status) to your running server via the web interface.
18| - **Automated Incremental Backups**: Schedule space-saving automatic backups for your game servers using native Linux hard links. Includes a 1-click restoration engine.
19| 
20| - **External Authentication**: Securely bridge your panel with external databases (like XenForo) to allow users to log in using their forum credentials. Features a strict Admin-only "Import User" feature to keep access clean and controlled.
21| - **Powerful REST API**: Admins can generate secure API keys to integrate their servers with Discord bots or external billing systems. The API supports fetching server data, starting/stopping/restarting servers, and more.
22| - **Integrated Support Tickets**: A modern, real-time chat interface for users to submit issues directly to administrators. Features dynamic status management (Open, Solved, Closed), optional closure reasons, and a live ticket timeline.
23| 
24| ---
25| 
26| ## 🛠️ Tech Stack
27| 
28| - **Backend**: Laravel 11 (PHP 8.2+)
29| - **Frontend**: Blade Templates, Vanilla CSS (Glassmorphism design), Feather Icons
30| - **Database**: MySQL / MariaDB
31| - **Server Communication**: `phpseclib3` (Secure SSH2 Integration)
32| 
33| ---
34| 
35| ## 🚀 Installation & Setup
36| 
37| ### Start Here
38| If you're setting this up on a VPS for the first time, follow the setup guide issue:
39| 
40| - [Setup Guide: How to Deploy SoF2Panel on a VPS](https://github.com/belalgalhom1/SoF2Panel/issues/1)
41| 
42| ### Prerequisites (Panel Server)
43| - PHP 8.2 or higher
44| - Composer
45| - MySQL or MariaDB
46| - Web Server (Nginx / Apache)
47| 
48| ### Prerequisites (Game Node / Host)
49| - A Linux VPS (Ubuntu/Debian recommended)
50| - `screen` installed (`apt install screen`)
51| - A base directory containing the SOF2 dedicated server files (e.g., `/opt/sof2_base`)
52| 
53| ### Installation Steps
54| 
55| 1. **Clone the repository**
56|    ```bash
57|    git clone https://github.com/belalgalhom1/sof2panel.git
58|    cd sof2panel
59|    ```
60| 
61| 2. **Install Dependencies**
62|    ```bash
63|    composer install
64|    npm install && npm run build
65|    ```
66| 
67| 3. **Set Permissions**
68|    Ensure your web server has write access to the storage and cache directories:
69|    ```bash
70|    chmod -R 775 storage bootstrap/cache
71|    chown -R www-data:www-data storage bootstrap/cache
72|    ```
73| 
74| 4. **Run the Setup Wizard**
75|    Simply point your web server (Nginx/Apache) to the `public` directory, and open your panel's URL in a web browser.
76|    The built-in **Setup Wizard** will automatically guide you through configuring your database, running migrations, and creating your first Admin account!
77| 
78| 5. **Start the Cron Worker (Crucial for Server Monitoring)**
79|    Add the following line to your server's crontab (`crontab -e`):
80|    ```bash
81|    * * * * * cd /var/www/sof2panel && php artisan schedule:run >> /dev/null 2>&1
82|    ```
83| 
84| ---
85| 
86| ## 💻 How It Works
87| 
88| SOF2Panel does not require a daemon to be installed on your game nodes. Instead, it uses a lightweight **agentless SSH architecture**:
89| 
90| 1. You add a **Host** (Linux VPS) to the panel along with its SSH credentials.
91| 2. When you create a **Server**, the panel connects to the Host via SSH.
| 92| 3. It automatically runs `useradd` to create an isolated Linux user for that specific server.
93| 4. It copies the base SOF2 files into the new user's `/home/username` directory.
94| 5. It launches the SOF2 server inside a detached `screen` session owned strictly by that user.
95| 6. To monitor the server, the Panel securely parses `screen -ls` and dynamically injects `rcon` packets to retrieve live player counts!
96| 
97| ---
98| 
99| ## 🛡️ Security
100| 
101| - Passwords (including Host SSH passwords, FTP passwords, and RCON passwords) are safely encrypted in the database using Laravel's AES-256-CBC encrypter.
102| - Server directories are completely isolated using native Linux user permissions (`chown`).
103| 
104| ---
105| 
106| ## 📝 License
107| 
108| This project is open-source and available under the [MIT License](LICENSE).
109| 
