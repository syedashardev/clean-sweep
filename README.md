# Clean-Sweep v0.2.1
**A Modular Database Architect Utility for High-Performance WordPress Systems.**

Most "optimization" plugins add more bloat than they remove. **Clean-Sweep** is a surgical utility built for architects who want direct control over database integrity and server memory without the overhead of heavy frameworks.

## 🚀 Key Features
* **Ghost Purge:** Identifies and securely deletes orphaned `postmeta` rows left behind by uninstalled or poorly coded plugins.
* **Autoload Auditor:** Scans the `wp_options` table to find the top 10 heaviest options loading into RAM on every single page request.
* **Autoload Offloader:** Securely toggles `autoload` from 'yes' to 'no' for heavy options, reclaiming server memory without deleting vital data.
* **Modular Architecture:** Organized using a "Separation of Concerns" (SoC) design pattern. Logic is decoupled from the UI for better scalability.

## 🛠 Why This Exists
WordPress performance bottlenecks are often invisible. If your `wp_options` table pre-loads 2MB of junk on every request, your TTFB (Time to First Byte) will suffer. Clean-Sweep provides the surgical tools to audit and offload that data using raw SQL for maximum speed.

## 🏗 Modular Structure
I have architected this plugin to follow professional development standards:
* `clean-sweep.php`: The Bootstrap file and initialization engine.
* `includes/class-clean-sweep-engine.php`: The Data Layer. Handles all raw `$wpdb` SQL interactions.
* `includes/class-clean-sweep-admin.php`: The UI Layer. Manages WordPress Dashboard integration, Security Nonces, and Rendering.

## 💻 Technical Highlights
* **PHP 8.x Compatible:** Uses Object-Oriented Programming (OOP) and Dependency Injection.
* **Standardized Documentation:** Fully documented using **PHPDoc** blocks for IDE compatibility and team collaboration.
* **Security First:** Every action is protected by WordPress Nonces and capability checks (`manage_options`).

## ⚠️ Warning
This is a high-level architect tool. Always perform a database backup before executing a "Purge" or "Offload" action.

---
**Architected by Ashar Fazail** *WordPress Systems Engineer with 5+ years of custom logic experience.* [Visit afashah.com](https://afashah.com) | [Connect on LinkedIn](https://www.linkedin.com/in/asharfazail/)