<div align="center">
  <h1>🎮 SOF2Panel</h1>
  <p><b>A modern, high-performance Game Server Management Panel built specifically for Soldier of Fortune 2.</b></p>
</div>

<br>

SOF2Panel is a premium, web-based management dashboard designed to seamlessly provision, control, and manage Soldier of Fortune 2 game servers across multiple Linux nodes. Built with Laravel 11, it features a stunning dark-mode glassmorphism UI, Granular User Permissions, Web FTP, and Live Player Monitoring.

## ✨ Key Features

- **Multi-Node Architecture**: Connect multiple Linux VPS instances via SSH. The panel acts as a master controller and provisions servers across your nodes instantly.
- **Automated Provisioning**: With a single click, SOF2Panel creates a dedicated Linux user, assigns ports, safely copies base game files, and spins up a dedicated `screen` session.
- **Granular Permissions**: Give your community managers access to specific servers. Control exactly what they can do (e.g., Allow "Start/Stop", but deny "Web FTP" and "RCON").
- **Live Player Monitoring**: View real-time player counts and server status directly from the dashboard.
- **Web FTP File Manager**: Browse, upload, download, and manage your `.cfg` and `.pk3` files directly from the browser without needing FileZilla.
- **Web RCON Console**: Instantly send RCON commands (kick, map change, status) to your running server via the web interface.
- **Automated Incremental Backups**: Schedule space-saving automatic backups for your game servers using native Linux hard links. Includes a 1-click restoration engine.

- **External Authentication**: Securely bridge your panel with external databases (like XenForo) to allow users to log in using their forum credentials. Features a strict Admin-only "Import User" workflow with live autocomplete search to keep your panel completely locked down.
- **Powerful REST API**: Admins can generate secure API keys to integrate their servers with Discord bots or external billing systems. The API supports fetching server data, starting/stopping/restarting servers, and sending RCON commands directly via HTTP requests—all completely logged and audited.
- **Integrated Support Tickets**: A modern, real-time chat interface for users to submit issues directly to administrators. Features dynamic status management (Open, Solved, Closed), optional closing reasons, and categorized support flows.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates, Vanilla CSS (Glassmorphism design), Feather Icons
- **Database**: MySQL / MariaDB
- **Server Communication**: `phpseclib3` (Secure SSH2 Integration)

---

## 🚀 Installation & Setup

### Prerequisites (Panel Server)
- PHP 8.2 or higher
- Composer
- MySQL or MariaDB
- Web Server (Nginx / Apache)

### Prerequisites (Game Node / Host)
- A Linux VPS (Ubuntu/Debian recommended)
- `screen` installed (`apt install screen`)
- A base directory containing the SOF2 dedicated server files (e.g., `/opt/sof2_base`)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/belalgalhom/sof2panel.git
   cd sof2panel
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Set Permissions**
   Ensure your web server has write access to the storage and cache directories:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

4. **Run the Setup Wizard**
   Simply point your web server (Nginx/Apache) to the `public` directory, and open your panel's URL in a web browser.
   The built-in **Setup Wizard** will automatically guide you through configuring your database, running migrations, and creating your first Admin account!

5. **Start the Cron Worker (Crucial for Server Monitoring)**
   Add the following line to your server's crontab (`crontab -e`):
   ```bash
   * * * * * cd /var/www/sof2panel && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## 💻 How It Works

SOF2Panel does not require a daemon to be installed on your game nodes. Instead, it uses a lightweight **agentless SSH architecture**:

1. You add a **Host** (Linux VPS) to the panel along with its SSH credentials.
2. When you create a **Server**, the panel connects to the Host via SSH.
3. It automatically runs `useradd` to create an isolated Linux user for that specific server.
4. It copies the base SOF2 files into the new user's `/home/username` directory.
5. It launches the SOF2 server inside a detached `screen` session owned strictly by that user.
6. To monitor the server, the Panel securely parses `screen -ls` and dynamically injects `rcon` packets to retrieve live player counts!

---

## 🛡️ Security

- Passwords (including Host SSH passwords, FTP passwords, and RCON passwords) are safely encrypted in the database using Laravel's AES-256-CBC encrypter.
- Server directories are completely isolated using native Linux user permissions (`chown`).

---

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).
