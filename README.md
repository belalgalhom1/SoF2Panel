<div align="center">
  <h1>🎮 SOF2Panel</h1>
  <p><b>A modern, high-performance Game Server Management Panel built specifically for Soldier of Fortune 2.</b></p>
</div>

<br>

SOF2Panel is a premium, web-based management dashboard designed to seamlessly provision, control, and manage Soldier of Fortune 2 game servers across multiple Linux nodes. Built with Laravel 11, it provides a modern server control panel for admins, community managers, and game hosts.

## ✨ Key Features

- **Multi-Node Architecture**: Connect multiple Linux VPS instances via SSH. The panel acts as a master controller and provisions servers across your nodes instantly.
- **Automated Provisioning**: With a single click, SOF2Panel creates a dedicated Linux user, assigns ports, safely copies base game files, and spins up a dedicated `screen` session.
- **Granular Permissions**: Give your community managers access to specific servers. Control exactly what they can do (e.g., Allow "Start/Stop", but deny "Web FTP" and "RCON").
- **Live Player Monitoring**: View real-time player counts and server status directly from the dashboard.
- **Web FTP File Manager**: Browse, upload, download, and manage your `.cfg` and `.pk3` files directly from the browser without needing FileZilla.
- **Web RCON Console**: Instantly send RCON commands (kick, map change, status) to your running server via the web interface.
- **Automated Incremental Backups**: Schedule space-saving automatic backups for your game servers using native Linux hard links. Includes a 1-click restoration engine.

- **External Authentication**: Securely bridge your panel with external databases (like XenForo) to allow users to log in using their forum credentials. Features a strict Admin-only "Import User" feature to keep access clean and controlled.
- **Powerful REST API**: Admins can generate secure API keys to integrate their servers with Discord bots or external billing systems. The API supports fetching server data, starting/stopping/restarting servers, and more.
- **Integrated Support Tickets**: A modern, real-time chat interface for users to submit issues directly to administrators. Features dynamic status management (Open, Solved, Closed), optional closure reasons, and a live ticket timeline.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates, Vanilla CSS (Glassmorphism design), Feather Icons
- **Database**: MySQL / MariaDB
- **Server Communication**: `phpseclib3` (Secure SSH2 Integration)

---

## 🚀 Quick Start

For complete installation and VPS setup instructions, see:

👉 **[Setup Guide: How to Deploy SoF2Panel on a VPS](https://github.com/belalgalhom1/SoF2Panel/issues/1)**

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
