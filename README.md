### Core Features (v0.1)
* **Autoload Auditor:** Identifies the top 10 heaviest options loading on every page.
* **Orphaned Meta Hunter:** Scans for "ghost" rows in `wp_postmeta` with no parent post.
* **Performance Focused:** Uses raw SQL via `$wpdb` to bypass slow WordPress wrappers.
* **Read-Only Safety:** No data is modified; this version is for diagnostic auditing only.
